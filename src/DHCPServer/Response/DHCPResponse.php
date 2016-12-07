<?php

namespace DHCPServer\Response;

use DHCPServer\DHCPConfig;
use DHCPServer\Postgresql;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

abstract class DHCPResponse
{

    /**
     * @var \DHCP\DHCPPacket
     */
    protected $packet;

    /**
     * @var ConsoleLogger
     */
    protected $logger;

    public function __construct($packet, LoggerInterface $logger)
    {
        $this->packet = $packet;
        $this->logger = $logger;
    }

    abstract public function respond(DHCPConfig $config);

    protected function createResponse($type)
    {
        $response = new \DHCP\DHCPPacket(false, $this->logger);
        $response->setOp(\DHCP\DHCPPacket::OP_BOOTREPLY);
        $response->setHtype($this->packet->getHtype());
        $response->setHlen($this->packet->getHlen());
        $response->setHops(0);
        $response->setXid($this->packet->getXid());
        $response->setSecs($this->packet->getSecs());
        $response->setFlags($this->packet->getFlags());
        $response->setCiaddr($this->packet->getCiaddr());
        $response->setChaddr($this->packet->getChaddr());
        $response->setMagiccookie($this->packet->getMagiccookie());

        $response->setType($type);

        return $response;
    }

    protected function findIpForClient($mac, DHCPConfig $dhcp_config, $requested_ip = false)
    {
        $database = new Postgresql();
        $static_ip = $database->getStaticIpByMac($mac);

        if ($static_ip) {
            $ip = $static_ip['ip']."/".DHCPConfig::mask2cidr($static_ip['mask']);
            if ($database->isFree($ip, $mac)) {
                if (!$static_ip['lease_time']) {
                    $static_ip['lease_time'] = 300;
                }
                $static_ip['mask'] = explode(".", $static_ip['mask']);
                $static_ip['router'] = explode(".", $static_ip['router']);
                $static_ip['dhcp'] = explode(".", $dhcp_config->getIp());
                $static_ip['broadcast'] = explode(".", $static_ip['broadcast']);

                return $static_ip;
            }
        }

        return array(
            'ip'         => $this->findDynamicIp($dhcp_config, $mac, $requested_ip),
            'mask'       => explode(".", $dhcp_config->getMask()),
            'router'     => explode(".", $dhcp_config->getRouter()),
            'dhcp'       => explode(".", $dhcp_config->getIp()),
            'dns'        => explode(".", implode(".", $dhcp_config->getDns())),
            'broadcast'  => explode(".", $dhcp_config->getBroadcast()),
            'lease_time' => $dhcp_config->getLeaseTime()
        );

    }

    protected function lockIp($selected_ip, $mac, $reason)
    {
        $this->logger->info(
            "Client $mac has locked {$selected_ip['ip']} for {$selected_ip['lease_time']}secs, reason: $reason"
        );

        $ip = $selected_ip['ip']."/".DHCPConfig::mask2cidr(implode(".", $selected_ip['mask']));
        (new Postgresql())->lockIp($ip, $selected_ip['lease_time'], $mac, $reason);
    }

    private function findDynamicIp(DHCPConfig $config, $mac, $requested_ip)
    {
        $database = new Postgresql();
        $currently_assigned = $database->getCurrentLease($mac);

        /**
         * todo: check if current lease is not on a static list for different mac (ie added to static after assigned to this host)
         * then: this host should not get a renewal on this lease
         */
        if ($currently_assigned) {
            $this->logger->debug("Client $mac has current lease on {$currently_assigned['ip']}");

            return $currently_assigned['ip'];
        }

        $max_static = $database->getNextDynamicIp($config->getNetwork());
        if ($max_static) {
            $this->logger->debug("Giving client $mac next ip after $max_static");
            $max_static = ip2long($max_static);

            return long2ip($max_static + 1);
        }

        //no static config and no current leases - get next ip after server
        $this->logger->debug("No active leases found, getting next ip after server");

        return long2ip(ip2long($config->getIp()) + 1);
    }
}