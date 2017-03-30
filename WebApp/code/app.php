<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once( __DIR__ . '/Medoo.php' ) ;
require_once( __DIR__ . '/api.php' ) ;
require_once( __DIR__ . '/host.php' ) ;
require_once( __DIR__ . '/frontend.php' ) ;
require_once( __DIR__ . '/admin.php' ) ;
require_once( __DIR__ . '/connectionInfo.php' ) ;

require_once( __DIR__ . '/phpCAS-master/CAS.php');


//session_start();

//initial database

// Using Medoo namespace
use Medoo\Medoo;
 
//utf8mb4
/*mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
header('Content-Type: text/html; charset=UTF-8');
*/

 
// Enjoy
/*$database->insert('account', [
    'user_name' => 'foo',
    'email' => 'foo@bar.com',
    'age' => 25,
    'lang' => ['en', 'fr', 'jp', 'cn']
]);
*/



class app{
    
    public static function database(){
        
        
        return new Medoo([
            'database_type' => DATABASE_TYPE,
            'database_name' => DATABASE_NAME,
            'server' => DATABASE_SERVER,
            'username' => DATABASE_USERNAME,
            'password' => DATABASE_PASSWORD,
            'charset' => DATABASE_CHARSET
        ]);
    }
    
   public static function print_header()
{

        
        return file_get_contents(__DIR__.'/static/header.html');
        
}

public static function print_footer(){
    return file_get_contents(__DIR__.'/static/footer.html');
}
    



}