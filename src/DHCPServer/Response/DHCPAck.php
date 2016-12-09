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

    public function respond(DHCPConfig $config)
    {
        $requested_ip = $this->packet->getOptions()->getOption(50);
        if ($requested_ip) {
            /** @var DHCPOption50 $requested_ip */
            $requested_ip = $requested_ip->getIp();
            $this->logger->info("Client requested ip: $requested_ip");
        } elseif ($this->packet->getCiaddr()) {
            $requested_ip = $this->packet->getCiaddr();
            $this->logger->info("Client sent ip: $requested_ip");
        }
        $selected_ip = $this->findIpForClient($this->packet->getChaddr(), $config, $requested_ip);

        if ($requested_ip && $requested_ip != $selected_ip['ip']) {
            $response = $this->createResponse(DHCPOption53::MSG_DHCPNAK);
            $response->setFlags(1); //broadcast
            $this->logger->info(
                "Sending Nak for request",
                array(
                    'mac' => $this->packet->getChaddr()
                )
            );

            return $response;
        }

        $response = $this->createResponse(DHCPOption53::MSG_DHCPACK);
        $response->setYiaddr($selected_ip['ip']);

        $lease = new DHCPOption51();
        $lease->setTime($selected_ip['lease_time']);
        $response->addOption($lease);

        $mask = new DHCPOption1();
        $mask->setMask($selected_ip['mask']);
        $response->addOption($mask);

        $router = new DHCPOption3();
        $router->setRouter($selected_ip['router']);
        $response->addOption($router);

        $dhcp = new DHCPOption54();
        $dhcp->setDHCP($selected_ip['dhcp']);
        $response->addOption($dhcp);

        $dns = new DHCPOption6();
        $dns->setDataFromList($selected_ip['dns']);
        $response->addOption($dns);

        $broadcast = new DHCPOption28();
        $broadcast->setBroadcast($selected_ip['broadcast']);
        $response->addOption($broadcast);

        $end = new DHCPOption255();
        $response->addOption($end);

//                $response->setOption(15, );
//                $response->setOption(12, );

        $this->logger->info(
            "Sending ack",
            array(
                'mac' => $this->packet->getChaddr(),
                'ip'  => $response->getYiaddr()
            )
        );

        $this->lockIp($selected_ip, $this->packet->getChaddr(), get_class());

        return $response;
    }
}
