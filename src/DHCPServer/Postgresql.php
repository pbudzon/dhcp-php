<?php

namespace DHCPServer;

/**
 * Class Postgresql
 *
 * @package DHCPServer
 *
 * @SuppressWarnings(PHPMD.ElseExpression)
 * @SuppressWarnings(PHPMD.ShortVariable)
 */
class Postgresql
{
    /**
     * @var resource
     */
    private $connection;

    public function __construct(DHCPConfig $config)
    {
        $this->connection = pg_connect("host=127.0.0.1 dbname=postgres user=root password=".$config->getDbPassword());
        if (!$this->connection) {
            throw new \Exception('Could not connect: '.pg_last_error());
        }
    }

    public function getStaticIpByMac($mac)
    {
        $result = pg_query_params("SELECT
            host(ip) as ip,
            netmask(ip) as mask,
            host(network(ip)) as network,
            host(broadcast(ip)) as broadcast,
            router, dns, lease_time
            FROM dhcp_static where mac = $1", array($mac));

        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row) {
            $row['dns'] = explode(",", str_replace(array("{", "}"), "", $row['dns']));
        }

        return $row;
    }

    public function isFree($ip, $mac)
    {
        if ($this->isCurrentLease($ip, $mac)) {
            return true;
        }

        $result = pg_query_params(
            "select id from dhcp_leases where ip =$1 and expires_on <= $2",
            array($ip, date("Y-m-d H:i:s"))
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row && !is_null($row['ip'])) {
            return false; //ip is taken
        }

        return true; //no active lease found
    }

    private function isCurrentLease($ip, $mac)
    {
        $result = pg_query_params(
            "select id from dhcp_leases where ip = $1 and mac=$2 and expires_on <= $3",
            array($ip, $mac, date("Y-m-d H:i:s"))
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row && !is_null($row['id'])) {
            return true;
        }

        return false;
    }

    public function getCurrentLease($mac)
    {
        $date = date("Y-m-d H:i:s");
        $result = pg_query_params(
            "select
            host(ip) as ip,
            netmask(ip) as mask,
            host(network(ip)) as network,
            host(broadcast(ip)) as broadcast
          from dhcp_leases where mac=$1 and expires_on > $2 order by assigned_on limit 1",
            array($mac, $date)
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row && !is_null($row['ip'])) {
            return $row;
        }

        return false;
    }

    public function lockIp($ip, $time, $mac, $reason)
    {
        $now = time();
        $assignedOn = date("Y-m-d H:i:s", $now);
        $expiresOn = date("Y-m-d H:i:s", $now + $time);

        $result = pg_query_params("select id from dhcp_leases where mac=$1 and ip = $2", array($mac, $ip));
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);

        if ($row) {
            $result = pg_query_params(
                "update dhcp_leases set expires_on=$1, reason=$2 where mac=$3 and ip=$4",
                array($expiresOn, $reason, $mac, $ip)
            );
            if (!$result) {
                throw new \Exception('Query failed: '.pg_last_error());
            }
        } else {
            $result = pg_query_params(
                "insert into dhcp_leases (mac,ip,assigned_on,expires_on,reason) values($1, $2, $3, $4, $5)",
                array($mac, $ip, $assignedOn, $expiresOn, $reason)
            );
            if (!$result) {
                throw new \Exception('Query failed: '.pg_last_error());
            }
        }

        pg_free_result($result);


        //expire all other active leases for this client
        $result = pg_query_params(
            "update dhcp_leases set expires_on=$1 where mac=$2 and ip!=$3 and expires_on > $4",
            array($assignedOn, $mac, $ip, $assignedOn)
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }
        pg_free_result($result);
    }

    public function expireIp($ip, $mac, $reason)
    {
        $expired = date("Y-m-d H:i:s");
        $result = pg_query_params(
            "update dhcp_leases set expires_on=$1, reason=$2 where mac=$3 and ip=$4 and expires_on > $5",
            array($expired, $reason, $mac, $ip, $expired)
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        pg_free_result($result);

        //expire all other active leases for this client
        $result = pg_query_params(
            "update dhcp_leases set expires_on=$1, reason=$2 where mac=$3 and ip!=$4 and expires_on > $5",
            array($expired, $reason, $mac, $ip, $expired)
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        pg_free_result($result);
    }

    public function getNextDynamicIp($network)
    {
        $staticIp = $this->getMaxStaticIpInNetwork($network);

        if ($staticIp) {
            $result = pg_query_params(
                "select max(host(ip)) ip from dhcp_leases where host(network(ip))=$1 and host(ip) > $2",
                array($network, $staticIp)
            );
            if (!$result) {
                throw new \Exception('Query failed: '.pg_last_error());
            }
        } else {
            $result = pg_query_params(
                "select max(host(ip)) ip from dhcp_leases where host(network(ip))=$1 and expires_on > $2",
                array($network, date("Y-m-d H:i:s"))
            );
            if (!$result) {
                throw new \Exception('Query failed: '.pg_last_error());
            }
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row && !is_null($row['ip'])) {
            return $row['ip'];
        }

        return $staticIp;
    }

    private function getMaxStaticIpInNetwork($network)
    {
        $result = pg_query_params(
            "select max(host(ip)) ip from dhcp_static where host(network(ip))=$1;",
            array($network)
        );
        if (!$result) {
            throw new \Exception('Query failed: '.pg_last_error());
        }

        $row = pg_fetch_assoc($result);

        pg_free_result($result);
        if ($row) {
            return $row['ip'];
        }

        return false;
    }
}
