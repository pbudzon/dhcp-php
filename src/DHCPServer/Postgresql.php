<?php

namespace DHCPServer;

class Postgresql {

    private $connection;

    public function __construct(){
        $this->connection = pg_connect("host=192.168.216.140 dbname=root user=root")
        or die('Could not connect: ' . pg_last_error());
    }

    public function getServerConfig(){
        $result = pg_query_params("SELECT * FROM dhcp_config", array())
        or die('Query failed: ' . pg_last_error());

        $rows = pg_fetch_all($result);

        pg_free_result($result);
        foreach($rows as $k => $row){
            $rows[$k]['dns'] = explode(",", str_replace(array("{", "}"), "", $row['dns']));
        }
        return $rows;
    }

    public function getIpByMac($mac){
        $result = pg_query_params("SELECT * FROM dhcp_static where mac = $1", array($mac))
        or die('Query failed: ' . pg_last_error());

        $row =  pg_fetch_assoc($result);

        pg_free_result($result);
        if($row){
            $row['dns'] = explode(",", str_replace(array("{", "}"), "", $row['dns']));
        }
        return $row;

    }
}