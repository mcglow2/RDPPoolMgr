<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of connectionInfo
 *
 * @author mcglow2
 */
class connectionInfo {
    //put your code here
    
    public static function formatStatus_use( $id ){
        switch( $id ){
            case 1:
                return "<span class='label label-success'>Connected</span>";
                break;
            case 2:
                return "<span class='label label-default'>Connecting</span>";
                break;
            case 3:
                return "<span class='label label-warning'>Disconnected</span>";
                break;
        }
        
        
    }
    
    public static function formatStatus_online( $id ){
        switch( $id ){
            case 1:
                return "<span class='label label-success'>Online</span>";
                break;
            case 0:
                return "<span class='label label-danger'>Offline</span>";
                break;
            
        }
        
        
    }
    
    public static function formatStatus_auth( $id ){
        switch( $id ){
            case 1:
                return "<span class='label label-success'>Yes</span>";
                break;
            case 0:
                return "<span class='label label-danger'>No</span>";
                break;
            
        }
        
        
    }
    
    public static function myActiveConnections( $username ){
        $database = app::database();
        
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
                'isInUse'=>[1,2,3],
                'currentUsername'=>$username
            ]);
            
        
        $html = "<table class='table' style='width:100%' >";
        
        $html .= "<tr>";
        $html .= "<th>Hostname</th>";
        $html .= "<th>Username</th>";
        $html .= "<th>Logon Time</th>";
        $html .= "<th>Status</th>";
        $html .= "<th></th>";

        $html .= "</tr>";
        
        foreach( $rows as $row ){
            $html .= "<tr>";
            $html .= "<td>".$row['hostname']."</td>";
            $html .= "<td>".$row['currentUsername']."</td>";
            $html .= "<td>".$row['currentUserLogon']."</td>";
            $html .= "<td>".connectionInfo::formatStatus_use($row['isInUse'])."</td>";
            $html .= "<td><a class='btn btn-primary' href='?method=connect&sid=".$row['sid']."'>Connect</a></td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";     
        
        return $html;
        
    }
    
    public static function myReservations( $username ){
        $database = app::database();
        
        $rows = $database->select('tblHosts', [
            "[>]tblAssignments"=>["sid"=>"sid"]
        ], [
                'tblHosts.sid',
                'tblHosts.ip',
                'tblHosts.hostname',
                'tblHosts.heartbeat',
                'tblHosts.currentUsername',
                'tblHosts.currentUserLogon',
                'tblHosts.isOnline',
                'tblHosts.isInUse'
            ],[
                'tblAssignments.username'=>$username
            ]);
            
        
        $html = "<table class='table' style='width:100%' >";
        
        $html .= "<tr>";
        $html .= "<th>Hostname</th>";
        $html .= "<th>Username</th>";
        $html .= "<th>Logon Time</th>";
        $html .= "<th>Host Status</th>";
        $html .= "<th></th>";
        $html .= "</tr>";
        
        foreach( $rows as $row ){
            $html .= "<tr>";
            $html .= "<td>".$row['hostname']."</td>";
            $html .= "<td>".$row['currentUsername']."</td>";
            $html .= "<td>".$row['currentUserLogon']."</td>";
            $html .= "<td>".connectionInfo::formatStatus_online($row['isOnline'])."</td>";
            $html .= "<td>".($row['isOnline']==1?"<a class='btn btn-primary' href='?method=connect&sid=".$row['sid']."'>Connect</a>":"")."</td>";
            $html .= "</tr>";
        }
        
        $html .= "</table>";     
        
        return $html;
        
    }
    
    
    public static function availableInPool( $pid ){
        $database = app::database();
        
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
                'poolId'=>$pid
            ]);
            
        
        $html = "<table class='table' style='width:100%' >";
        
        $html .= "<tr>";
        $html .= "<th>Host Name</th>";
        $html .= "<th>Heartbeat</th>";
        $html .= "<th>Username</th>";
        $html .= "<th>Logon Time</th>";
        $html .= "<th>Online</th>";
        $html .= "<th>In Use</th>";
        $html .= "<th></th>";

        $html .= "</tr>";
        
        foreach( $rows as $row ){
            $html .= "<tr>";
            $html .= "<td>".$row['hostname']."</td>";
            $html .= "<td>".$row['heartbeat']."</td>";
            $html .= "<td>".$row['currentUsername']."</td>";
            $html .= "<td>".$row['currentUserLogon']."</td>";
            $html .= "<td>".connectionInfo::formatStatus_online( $row['isOnline'] )."</td>";
            $html .= "<td>".connectionInfo::formatStatus_use($row['isInUse'])."</td>";
            $html .= "<td>".($row['isOnline']==1?"<a class='btn btn-primary' href='?method=connect&sid=".$row['sid']."'>Connect</a>":"")."</td>";
            
            $html .= "</tr>";
        }
        
        $html .= "</table>";     
        
        return $html;
        
    }
    
    public static function availablePools( $username ){
        $database = app::database();
        
        
        $rows = $database->query("SELECT pid, label, `desc` ,
 SUM(IF( tblHosts.isOnline = 1, 1 , 0 )) AS `online` , 
SUM(IF( tblHosts.isInUse != 0, 1 , 0 )) AS `inUse` 
FROM tblPool LEFT JOIN tblHosts ON tblPool.pid = tblHosts.poolId
WHERE isEnabled = 1
GROUP BY pid,label,`desc`");
        
        
        $html = "<table class='table' style='width:100%' >";
        
        $html .= "<tr>";
        $html .= "<th>Pool Name</th>";
        $html .= "<th>Available</th>";
        $html .= "<th>In Use</th>";

        $html .= "<th></th>";

        $html .= "</tr>";
        
        foreach( $rows as $row ){
            $html .= "<tr>";
            $html .= "<td><a href='?method=poollist&pid=".$row['pid']."'>".$row['label']."</a></td>";
            $html .= "<td>".$row['online']."</td>";
            $html .= "<td>".$row['inUse']."</td>";

            $html .= "<td>".($row['online']-$row['inUse']?"<a class='btn btn-primary' href='?method=connect&pid=".$row['pid']."'>Connect</a>":"")."</td>";
            
            $html .= "</tr>";
        }
        
        $html .= "</table>";     
        
        return $html;
        
    }
    
    
    public static function allWorkstations( ){
        $database = app::database();
        
        $rows = $database->select('tblHosts', [
            "[>]tblAssignments"=>["sid"=>"sid"]
        ], [
                'tblHosts.sid',
                'tblHosts.ip',
                'tblHosts.hostname',
                'tblHosts.winname',
                'tblHosts.heartbeat',
                'tblHosts.currentUsername',
                'tblHosts.currentUserLogon',
                'tblHosts.isOnline',
                'tblHosts.isInUse',
                'tblHosts.isAuthorized'
            ]);
            
        
        $html = "<table class='table' style='width:100%' >";
        
        $html .= "<tr>";
        $html .= "<th>Hostname</th>";
         $html .= "<th>NetBios Name</th>";
        $html .= "<th>Heartbeat</th>";
        $html .= "<th>Authorized</th>";
        $html .= "<th>Host</th>";
        $html .= "<th>User</th>";        
        $html .= "<th>Username</th>";
        $html .= "<th>Logon Time</th>";


        $html .= "</tr>";
        
        foreach( $rows as $row ){
            $html .= "<tr>";
            $html .= "<td>".$row['hostname']."</td>";
            $html .= "<td>".$row['winname']."</td>";
            $html .= "<td>".$row['heartbeat']."</td>";
            
            $html .= "<td>".connectionInfo::formatStatus_auth($row['isAuthorized'])."</td>";
            
            $html .= "<td>".connectionInfo::formatStatus_online($row['isOnline'])."</td>";
            
            $html .= "<td>".connectionInfo::formatStatus_use($row['isInUse'])."</td>";
            $html .= "<td>".$row['currentUsername']."</td>";
            $html .= "<td>".($row['isInUse']==1?$row['currentUserLogon']:"")."</td>";

            $html .= "</tr>";
        }
        
        $html .= "</table>";     
        
        return $html;
        
    }
    
}
