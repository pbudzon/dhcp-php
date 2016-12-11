<?php
namespace DHCPServer\Response;

use DHCP\Options\DHCPOption1;
use DHCP\Options\DHCPOption255;
use DHCP\Options\DHCPOption51;
use DHCP\Options\DHCPOption53;
use DHCP\Options\DHCPOption54;
use DHCPServer\DHCPConfig;

class DHCPOffer extends DHCPResponse
{
    public function respond(DHCPConfig $config)
    {
        $selected_ip = $this->findIpForClient($this->packet->getChaddr(), $config);

        $response = $this->createResponse(DHCPOption53::MSG_DHCPOFFER);
        $response->setYiaddr($selected_ip['ip']);
        $response->setCiaddr($selected_ip['ip']); //seems like mac/windows require this?

        $lease = new DHCPOption51();
        $lease->setTime($selected_ip['lease_time']);
        $response->addOption($lease);

        $mask = new DHCPOption1();
        $mask->setMask($selected_ip['mask']);
        $response->addOption($mask);

        $dhcp = new DHCPOption54();
        $dhcp->setIdentifier($selected_ip['dhcp']);
        $response->addOption($dhcp);

        $end = new DHCPOption255();
        $response->addOption($end);

        $this->logger->info(
            "Sending offer",
            array(
                'mac' => $this->packet->getChaddr(),
                'ip'  => $response->getYiaddr()
            )
        );

        $this->lockIp($selected_ip, $this->packet->getChaddr(), get_class());

        return $response;
    }
}
