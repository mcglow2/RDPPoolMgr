<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once( '../../../config.php' );
require_once( '../../../code/app.php' );

$api = new api();
$api->hostname = gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) ;
        
switch( $_GET['method'] ){
    case 'cleanup':
        $api->cleanup();
        break;
    case 'join':
        $api->join( $_SERVER['REMOTE_ADDR']);
        break;
    case 'leave':
        $api->leave( );
        break;
    case 'logon':
        $username = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", htmlspecialchars($_GET['username']))) ;
        $api->logon( $username );
        break;
    case 'logoff':
        $username = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", htmlspecialchars($_GET['username']))) ;
        $api->logoff( $username );
        break;
    case 'disconnect':
        $username = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", htmlspecialchars($_GET['username']))) ;
        $api->disconnect( $username );
        break;
    case 'log':
        //$message = preg_replace("/[^a-zA-Z0-9_\s]+/", "", htmlspecialchars($_GET['message'])) ;
        $message =  htmlspecialchars($_GET['message']) ;
        $username = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", htmlspecialchars($_GET['username']))) ;
        $api->log( $message , $username );
        break;
    case 'heartbeat':
        $api->markHearbeat();
        break;
}

echo "OK";