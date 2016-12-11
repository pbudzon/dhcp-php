<?php
namespace DHCPServer\Response;

use DHCP\Options\DHCPOption1;
use DHCP\Options\DHCPOption28;
use DHCP\Options\DHCPOption3;
use DHCP\Options\DHCPOption50;
use DHCP\Options\DHCPOption51;
use DHCP\Options\DHCPOption53;
use DHCP\Options\DHCPOption54;
use DHCP\Options\DHCPOption6;
use DHCP\Options\DHCPOption255;
use DHCPServer\DHCPConfig;

class DHCPAck extends DHCPResponse
{

    public function respond()
    {
        $requestedIp = $this->packet->getOptions()->getOption(50);
        if ($requestedIp) {
            /** @var DHCPOption50 $requestedIp */
            $requestedIp = $requestedIp->getIp();
            $this->logger->info("Client requested ip: $requestedIp");
        } elseif ($this->packet->getCiaddr()) {
            $requestedIp = $this->packet->getCiaddr();
            $this->logger->info("Client sent ip: $requestedIp");
        }
        $selectedIp = $this->findIpForClient($this->packet->getChaddr(), $requestedIp);

        if ($requestedIp && $requestedIp != $selectedIp['ip']) {
            $response = $this->createResponse(DHCPOption53::MSG_DHCPNAK);
            $response->setFlags(1); //broadcast
            $end = new DHCPOption255();
            $response->addOption($end);

            $this->logger->info(
                "Sending Nak for request",
                array(
                    'mac' => $this->packet->getChaddr()
                )
            );

            return $response;
        }

        $response = $this->createResponse(DHCPOption53::MSG_DHCPACK);
        $response->setYiaddr($selectedIp['ip']);

        $lease = new DHCPOption51();
        $lease->setTime($selectedIp['lease_time']);
        $response->addOption($lease);

        $mask = new DHCPOption1();
        $mask->setMask($selectedIp['mask']);
        $response->addOption($mask);

        $router = new DHCPOption3();
        $router->setRouter($selectedIp['router']);
        $response->addOption($router);

        $dhcp = new DHCPOption54();
        $dhcp->setIdentifier($selectedIp['dhcp']);
        $response->addOption($dhcp);

        $dns = new DHCPOption6();
        $dns->setDataFromList($selectedIp['dns']);
        $response->addOption($dns);

        $broadcast = new DHCPOption28();
        $broadcast->setBroadcast($selectedIp['broadcast']);
        $response->addOption($broadcast);

        $end = new DHCPOption255();
        $response->addOption($end);

        $this->logger->info(
            "Sending ack",
            array(
                'mac' => $this->packet->getChaddr(),
                'ip'  => $response->getYiaddr()
            )
        );

        $this->lockIp($selectedIp, $this->packet->getChaddr(), get_class());

        return $response;
    }

    public function release()
    {
        $this->logger->debug("Received release for: ".$this->packet->getChaddr());
        $this->releaseIp($this->packet->getYiaddr(), $this->packet->getChaddr(), 'Release');
    }
}
