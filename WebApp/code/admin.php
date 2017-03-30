<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class admin{
    
    public function process(){
        
        
        // Enable debugging
        //phpCAS::setDebug();
        // Enable verbose error messages. Disable in production!
        //phpCAS::setVerbose(true);
        
        // Initialize phpCAS
        phpCAS::client(CAS_VERSION_2_0, CAS_HOST , CAS_PORT , CAS_CONTEXT );
        
        //phpCAS::setNoCasServerValidation();
        
        // force CAS authentication
        phpCAS::forceAuthentication();
        
        $method = null;
        if( isset($_GET['method']) ){
            $method = $_GET['method'];
        }
        
        switch( $method ){
            case 'log':
                $result = $this->log();
                break;
            case 'status':
            default:
                $result = $this->status();
                break;
        }

        return $result;
    }
    
    
    public function status(){
        $html = "";
        $html .= app::print_header();
        
        $html .= "<h1>Admin Status</h1>";
        
        $username = strtolower( phpCAS::getUser() ); //$_SERVER['REMOTE_USER'];
        
        
        
        $html .= "Username: $username";
        
        //Show active connections
        $html .= "<h2>All Workstations</h2>";
        $html .= connectionInfo::allWorkstations();
        
     
        
        $html .= app::print_footer();
        
        return $html;
    }
    
    
    
}