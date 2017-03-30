<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class host{
    
    public static function createHost( $ip , $hostname ){
        
        $database = app::database();
        
        $database->insert('tblHosts', [
            'ip' => $ip,
            'hostname' => $hostname,
            'isOnline'=>1,
            'isInUse'=>0
        ]);
        
        
        
    }
    
    
    public static function getSid( $hostname ){
        $database = app::database();
        
        $data = $database->select('tblHosts', [
            'sid'
        ],[
            'hostname[=]'=>$hostname
        ]);
        
        if( isset( $data[0]['sid'] )){
            return $data[0]['sid'];
        }else{
            return 0;
        }
        
    }
    
    public static function getHostnameFromSid( $sid ){
        $database = app::database();
        
        $data = $database->select('tblHosts', [
            'hostname'
        ],[
            'sid[=]'=>$sid
        ]);
        
        return $data[0]['hostname'];
        
    }
    
    public static function getHostnameFromPool( $pid ){
        $database = app::database();
        
        $data = $database->select('tblHosts', [
            'hostname'
        ],[
            'poolId[=]'=>$pid,
            'isOnline'=>1,
            'isAuthorized'=>1,
            'isInUse'=>0
        ]);
        
        return $data[0]['hostname'];
        
    }
    
    public static function getHost( $username ){
        $server = "";
        $database = app::database();
        //check for current session
        if( $username != "" ){
            $data = $database->select('tblHosts', [
                'hostname'
            ],[
                'isOnline'=>1,
                'isAuthorized'=>1,
                'isInUse'=>[1,2,3],
                'currentUsername'=>$username
            ]);
            
            if( isset( $data[0]) ){
                $server = $data[0]['hostname'];
            }
            
            if( $server != ""  ){
                return $server;
            }
        }
        
        //Check for static user host
        if( $username != "" ){
            
            $data = $database->select('tblHosts', [
                'hostname'
            ],[
                'isOnline'=>1,
                'isAuthorized'=>1,
                'isInUse'=>[1,2,3],
                'currentUsername'=>$username
            ]);
            
            if( isset( $data[0]) ){
                $server = $data[0]['hostname'];
            }
            
            if( $server != ""  ){
                return $server;
            }
            
        }

        
            
        $data = $database->select('tblHosts', [
            'hostname'
        ],[
            'isOnline'=>1,
            'isAuthorized'=>1,
            'isInUse'=>0
        ]); //@todo:  should probably check heart beat here too

        //Randomize host
        $row = $data[array_rand($data)];

        $server = $row['hostname'];
            
        

        return $server;
    }
    
    
}