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
    public function respond()
    {
        $selectedIp = $this->findIpForClient($this->packet->getChaddr());

        $response = $this->createResponse(DHCPOption53::MSG_DHCPOFFER);
        $response->setYiaddr($selectedIp['ip']);
        $response->setCiaddr($selectedIp['ip']); //seems like mac/windows require this?

        $lease = new DHCPOption51();
        $lease->setTime($selectedIp['lease_time']);
        $response->addOption($lease);

        $mask = new DHCPOption1();
        $mask->setMask($selectedIp['mask']);
        $response->addOption($mask);

        $dhcp = new DHCPOption54();
        $dhcp->setIdentifier($selectedIp['dhcp']);
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

        $this->lockIp($selectedIp, $this->packet->getChaddr(), get_class());

        return $response;
    }
}
