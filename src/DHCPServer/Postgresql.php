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

    public function getStaticIpByMac($mac){
        $result = pg_query_params("SELECT
            host(ip) as ip,
            netmask(ip) as mask,
            host(network(ip)) as network,
            host(broadcast(ip)) as broadcast,
            router, dns, lease_time
            FROM dhcp_static where mac = $1", array($mac))
        or die('Query failed: ' . pg_last_error());

        $row =  pg_fetch_assoc($result);

        pg_free_result($result);
        if($row){
            $row['dns'] = explode(",", str_replace(array("{", "}"), "", $row['dns']));
        }
        return $row;
    }

    public function isFree($ip, $mac){
        if($this->isCurrentLease($ip, $mac)){
            return true;
        }

        $result = pg_query_params("select id from dhcp_leases where ip =$1 and expires_on <= $2", array($ip, date("Y-m-d H:i:s")))
        or die('Query failed: ' . pg_last_error());

        $row =  pg_fetch_assoc($result);

        pg_free_result($result);
        if($row && !is_null($row['ip'])){
            return false; //ip is taken
        }
        return true; //no active lease found
    }

    private function isCurrentLease($ip, $mac){
        $result = pg_query_params("select id from dhcp_leases where ip = $1 and mac=$2 and expires_on <= $3", array($ip, $mac, date("Y-m-d H:i:s")))
        or die('Query failed: ' . pg_last_error());

        $row =  pg_fetch_assoc($result);

        pg_free_result($result);
        if($row && !is_null($row['id'])){
            return true;
        }
        return false;
    }

    public function getCurrentLease($mac){
        $result = pg_query_params("select
            host(ip) as ip,
            netmask(ip) as mask,
            host(network(ip)) as network,
            host(broadcast(ip)) as broadcast
          from dhcp_leases where mac=$1 and expires_on > $2 order by assigned_on limit 1", array($mac, date("Y-m-d H:i:s")))
        or die('Query failed: ' . pg_last_error());

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if($row && !is_null($row['ip'])){
            return $row;
        }
        return false;
    }

    public function lockIp($ip, $time, $mac, $reason){
        $now = time();
        $assigned_on = date("Y-m-d H:i:s", $now);
        $expires_on = date("Y-m-d H:i:s", $now+$time);

        $result = pg_query_params("select id from dhcp_leases where mac=$1 and ip = $2", array($mac, $ip))
        or die('Query failed: ' . pg_last_error());

        $row = pg_fetch_assoc($result);

        pg_free_result($result);

        if($row){
            $result = pg_query_params("update dhcp_leases set expires_on=$1, reason=$2 where mac=$3 and ip=$4", array($expires_on,$reason, $mac, $ip))
            or die('Query failed: ' . pg_last_error());
        }
        else{
            $result = pg_query_params("insert into dhcp_leases (mac,ip,assigned_on,expires_on,reason) values($1, $2, $3, $4, $5)", array($mac, $ip, $assigned_on, $expires_on,$reason))
            or die('Query failed: ' . pg_last_error());
        }

        pg_free_result($result);


        //expire all other active leases for this client
        $result = pg_query_params("update dhcp_leases set expires_on=$1 where mac=$2 and ip!=$3 and expires_on > $4", array($assigned_on, $mac, $ip, $assigned_on))
        or die('Query failed: ' . pg_last_error());
        pg_free_result($result);
    }

    public function getNextDynamicIp($network){
        $static_ip = $this->getMaxStaticIpInNetwork($network);

        $result = pg_query_params("select max(host(ip)) ip from dhcp_leases where host(network(ip))=$1 and host(ip) > $2", array($network, $static_ip))
        or die('Query failed: ' . pg_last_error());

        $row =  pg_fetch_assoc($result);

        pg_free_result($result);
        if($row && !is_null($row['ip'])){
            return $row['ip'];
        }
        return $static_ip;
    }

    private function getMaxStaticIpInNetwork($network){
        $result = pg_query_params("select max(host(ip)) ip from dhcp_static where host(network(ip))=$1;", array($network))
        or die('Query failed: ' . pg_last_error());

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if($row){
            return $row['ip'];
        }
        return false;
    }
}