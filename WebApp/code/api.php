<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class api{
    
    public $hostname = "";
    
    public function cleanup(){
        $database = app::database();
        
        //clean up routine 
    //   Bounce isUse = 2 currentUserLogon > 5 minutes, give 5 minutes for RDP to connect
            $rows = $database->select('tblHosts', [
                'sid',
                'ip',
                'hostname',
                'heartbeat',
                'currentUsername',
                'currentUserLogon',
                'isOnline',
                'isInUse'
            ],[
                'isInUse'=>2,
                'currentUserLogon[<]'=>date('Y-m-d H:i:s',time() - 10 * 60)
            ]);
        
        
        foreach( $rows as $row ){
            $database->update(
            'tblHosts',
            [
                'isInUse'=>0,
                'currentUsername'=>''
            ],            
            [
                'sid' => $row['sid'],
                'isInUse'=>2
            ]
        );
        }
  
    //      Check pool and release inactive after some time
    
        

//   Check heartbeats and set isOnline to 0 if no heartbeat in 10 minutes
            $rows = $database->select('tblHosts', [
                'sid',
                'ip',
                'hostname',
                'heartbeat',
                'currentUsername',
                'currentUserLogon',
                'isOnline',
                'isInUse'
            ],[
                'isOnline'=>1,
                'isAlwaysOnline'=>0,
                'heartbeat[<]'=>date('Y-m-d H:i:s',time() - 10 * 60)
            ]);
        
        
        foreach( $rows as $row ){
            $database->update(
            'tblHosts',
            [
                'isOnline'=>0,
                'heartbeat'=>'',
                'currentUsername'=>''
            ],            
            [
                'sid' => $row['sid'],
                'isOnline'=>1
            ]
        );
        }
            
        $this->log('cleanup');
    }
    
    public function join( $ip ){
        
        $h = new host();
        $h->createHost( $ip , $this->hostname  );
        
        $database = app::database();
        
        //mark host online
        $database->update(
            'tblHosts',
            [
                'isOnline'=>1,
                'currentUsername'=>''
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        
        $this->log('join');
    }
    
    public function leave(){
        $database = app::database();
        
        //mark host offline
        $database->update(
            'tblHosts',
            [
                'isOnline'=>0
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        $this->log('leave');
    }
    
    public function log( $message , $username = '' ){
        $database = app::database();
        
        $sid =  host::getSid($this->hostname) ;
        
        //record user access
        $database->insert(
            'tblUsageLog',
            [
                'usageDate'=>date("Y-m-d H:i:s"),
                'username'=>$username,
                'note'=>$message,
                'sid'=>$sid
            ]
        );
        
    }
    
    public function logon( $username ){
        $database = app::database();
        
        //mark host in use
        $database->update(
            'tblHosts',
            [
                'isInUse'=>1,
                'currentUserLogon'=>date("Y-m-d H:i:s"),
                'currentUsername'=>$username
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        
        $sid =  host::getSid($this->hostname) ;
        
        //record user access
        $database->insert(
            'tblUsageLog',
            [
                'usageDate'=>date("Y-m-d H:i:s"),
                'username'=>$username,
                'note'=>'logon',
                'sid'=>$sid
            ]
        );
        
    }
    
    public function logoff( $username ){
        $database = app::database();
        
        //mark host in use
        $database->update(
            'tblHosts',
            [
                'isInUse'=>0,
                'currentUsername'=>''
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        
        $sid =  host::getSid($this->hostname) ;
        
        //record user access
        $database->insert(
            'tblUsageLog',
            [
                'usageDate'=>date("Y-m-d H:i:s"),
                'username'=>$username,
                'note'=>'logoff',
                'sid'=>$sid
            ]
        );
        
    }
    
    public function disconnect( $username ){
        $database = app::database();
        
        //mark host in use
        $database->update(
            'tblHosts',
            [
                'isInUse'=>3
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        
        $sid =  host::getSid($this->hostname) ;
        
        //record user access
        $database->insert(
            'tblUsageLog',
            [
                'usageDate'=>date("Y-m-d H:i:s"),
                'username'=>$username,
                'note'=>'disconnect',
                'sid'=>$sid
            ]
        );
        
    }
    
    public function markHearbeat(){
        $database = app::database();
        
        $database->update(
            'tblHosts',
            [
                'heartbeat'=>date("Y-m-d H:i:s"),
                'isOnline'=>1
            ],            
            [
                'hostname' => $this->hostname
            ]
        );
        
    }
    
    
    
    
}