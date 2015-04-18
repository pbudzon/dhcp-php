<?php

namespace DHCPServer\Response;

use DHCPServer\Postgresql;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;

abstract class DHCPResponse {

    /**
     * @var \DHCP\DHCPPacket
     */
    protected $packet;

    /**
     * @var ConsoleLogger
     */
    protected $logger;

    public function __construct($packet, LoggerInterface $logger){
        $this->packet = $packet;
        $this->logger = $logger;
    }

    abstract public function respond($dhcp_config);

    protected function createResponse($type){
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

    protected function findIpForClient($mac, $dhcp_config, $requested_ip = false){
        $database = new Postgresql();
        $static_ip = $database->getIpByMac($mac);

        if($static_ip){
            if(!$static_ip['lease_time']){
                $static_ip['lease_time'] = 300;
            }
            $static_ip['mask'] = explode(".", $static_ip['mask']);
            $static_ip['router'] = explode(".", $static_ip['router']);
            $static_ip['dhcp'] = explode(".", $dhcp_config['server_ip']);
            $static_ip['broadcast'] = explode(".", $static_ip['broadcast']);

            return $static_ip;
        }
        else{
            //use $dhcp_config

            return array(
                'ip' => '10.0.1.23',
                'mask' =>  array(255, 255, 255, 0),
                'router' => array(10, 0, 1, 1),
                'dhcp' => array(10, 0, 1, 1),
                'dns' => array(8, 8, 8, 8, 8, 8, 4, 4),
                'broadcast' => array(10, 0, 1, 255),
                'lease_time' => 300
            );
        }
    }
}