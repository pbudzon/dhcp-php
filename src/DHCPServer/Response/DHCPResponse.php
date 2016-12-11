<?php

namespace DHCPServer\Response;

use DHCPServer\DHCPConfig;
use DHCPServer\Postgresql;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

/**
 * Class DHCPResponse
 *
 * @package DHCPServer\Response
 *
 * @SuppressWarnings(PHPMD.ShortVariable)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
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

    /**
     * @var DHCPConfig
     */
    protected $config;

    public function __construct($packet, DHCPConfig $config, LoggerInterface $logger)
    {
        $this->packet = $packet;
        $this->logger = $logger;
        $this->config = $config;
    }

    abstract public function respond();

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

    protected function findIpForClient($mac, $requestedIp = null)
    {
        $database = new Postgresql($this->config);
        $staticIp = $database->getStaticIpByMac($mac);

        if ($staticIp) {
            $ip = $staticIp['ip']."/".DHCPConfig::mask2cidr($staticIp['mask']);
            if ($database->isFree($ip, $mac)) {
                if (!$staticIp['lease_time']) {
                    $staticIp['lease_time'] = 300;
                }
                $staticIp['dhcp'] = $this->config->getIpAddress();

                return $staticIp;
            }
        }

        return array(
            'ip'         => $this->findDynamicIp($mac, $requestedIp),
            'mask'       => $this->config->getMask(),
            'router'     => $this->config->getRouter(),
            'dhcp'       => $this->config->getIpAddress(),
            'dns'        => $this->config->getDns(),
            'broadcast'  => $this->config->getBroadcast(),
            'lease_time' => $this->config->getLeaseTime()
        );
    }

    protected function lockIp($selectedIp, $mac, $reason)
    {
        $this->logger->info(
            "Client $mac has locked {$selectedIp['ip']} for {$selectedIp['lease_time']}secs, reason: $reason"
        );

        $ip = $selectedIp['ip']."/".DHCPConfig::mask2cidr($selectedIp['mask']);
        (new Postgresql($this->config))->lockIp($ip, $selectedIp['lease_time'], $mac, $reason);
    }

    protected function releaseIp($ip, $mac, $reason)
    {
        (new Postgresql($this->config))->expireIp($ip, $mac, $reason);
    }

    private function findDynamicIp($mac, $requestedIp)
    {
        $database = new Postgresql($this->config);
        $currently_assigned = $database->getCurrentLease($mac);

        /**
         * todo: check if current lease is not on a static list for different mac (ie added to static after assigned to this host)
         * then: this host should not get a renewal on this lease
         */
        if ($currently_assigned) {
            $this->logger->debug("Client $mac has current lease on {$currently_assigned['ip']}");

            return $currently_assigned['ip'];
        }

        /**
         * todo: respect $requestedIp when possible
         */
        $max_static = $database->getNextDynamicIp($this->config->getNetwork());
        if ($max_static) {
            $this->logger->debug("Giving client $mac next ip after $max_static (request: $requestedIp)");
            $max_static = ip2long($max_static);

            return long2ip($max_static + 1);
        }

        //no static config and no current leases - get next ip after server
        $this->logger->debug("No active leases found, getting next ip after server");

        return long2ip(ip2long($this->config->getIpAddress()) + 1);
    }
}
