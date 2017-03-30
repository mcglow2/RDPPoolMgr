<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class frontend{
    
    
   /* public function isAuthorized(){
        
        if( isset($_SESSION['isLoggedIn']) ){
            return true;
        }else{
            
            
            $_SESSION['isLoggedIn'] 
        }
        
        
        
    }*/


    public function process(){
        
        // Enable debugging
        //phpCAS::setDebug();
        // Enable verbose error messages. Disable in production!
        //phpCAS::setVerbose(true);
        
        // Initialize phpCAS
        phpCAS::client(CAS_VERSION_2_0, CAS_HOST , CAS_PORT , CAS_CONTEXT );
        
        phpCAS::setNoCasServerValidation();
        
        // force CAS authentication
        phpCAS::forceAuthentication();
        
        $method = null;
        if( isset($_GET['method']) ){
            $method = $_GET['method'];
        }
        
        switch( $method ){
            case 'connect':
                $result = $this->connectToRDP();
                break;
            case 'poollist':
                $result = $this->poollist();
                break;
            case 'status':
            default:
                $result = $this->status();
                break;
        }

        return $result;
        //Faculty research machines
          //Pool of Username to IP mappings, user gets own system
          //Script to create Desktop, Grant admin access


        //Shared use machines
            //Secure machine to machine communication
            //Auto add to pool when booted
            //Track in use/ not in use

        //Base all security on source IP address, machine can only lie about its self


        ///PHP_AUTH_USER
        //REMOTE_USER
        //[AUTHENTICATE_SAMACCOUNTNAME] => MCGLOW2
         //   [AUTHORIZE_SAMACCOUNTNAME] => MCGLOW2
    }
    
    public function connectToRDP(){
        //$server = "vcmr-dc4.win.rpi.edu";
        
        $username = strtolower( phpCAS::getUser()) ; //$_SERVER['REMOTE_USER'];
        
        if( isset($_GET['sid']) ){ //switch to non quessable tokens, possibly include username to prevent sharing
            
            $server = host::getHostnameFromSid($_GET['sid']);
        }else if( isset($_GET['pid']) ){ 
            
            $server = host::getHostnameFromPool($_GET['pid']);
        }else{
            $server = host::getHost($username);
        }
        

        if( $server == "" ){
            frontend::errorToUser("No servers available");
        }
        
        //die($server);
        
        //Mark server as pending rdp connection
        $database = app::database();
        $database->update('tblHosts', [
            'isInUse'=>2,
            'currentUserLogon'=>date("Y-m-d H:i:s"),
            'currentUsername'=>$username
        ],[
            'hostname'=>$server
        ]); 
        
        
        
        //Send RDP connection
        $data = file_get_contents('default.rdp');
        $data = str_replace('%SERVER%', $server , $data );
        header('Content-Type: application/rdp');
        header("Content-Disposition: inline; filename=rcs.rdp");
        echo $data;
        
    }
    
    public function errorToUser( $message ){
        $html = "";
        $html .= app::print_header();
        
        $html .= "$message";
        
        
        $html .= app::print_footer();
        
        die( $html );
    }
    
    public function status(){
        $html = "";
        $html .= app::print_header();
        
        $html .= "<h1>Status</h1>";
        
        $username = strtolower( phpCAS::getUser() ); //$_SERVER['REMOTE_USER'];
        
        $html .= "Username: $username";
        
        //Show active connections
        $html .= "<h2>Active Connections</h2>";
        $html .= connectionInfo::myActiveConnections($username);
        
        //Show assigned workstation
        $html .= "<h2>Reserved Workstations</h2>";
        $html .= connectionInfo::myReservations($username);        
        
        //Show pools
        $html .= "<h2>Available Workstation Pools</h2>";
        $html .= connectionInfo::availablePools($username);
        
        $html .= app::print_footer();
        
        return $html;
    }
    
    public function poollist(){
        $html = "";
        $html .= app::print_header();
        
        $html .= "<h1>Pool List</h1>";
        
        $pid = intval($_GET['pid']);
        
        $html .= connectionInfo::availableInPool($pid);
        
        $html .= app::print_footer();
        
        return $html;
    }
    
    
}