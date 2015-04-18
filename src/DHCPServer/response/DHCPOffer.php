<?php
namespace DHCPServer\Response;

use DHCP\DHCPPacket;
use DHCP\Options\DHCPOption53;
use DHCPServer\Postgresql;

class DHCPOffer extends DHCPResponse{


    public function respond($dhcp_config){
        $selected_ip = $this->findIpForClient($this->packet->getChaddr(), $dhcp_config);

        $response = $this->createResponse(DHCPOption53::MSG_DHCPOFFER);
        $response->setYiaddr($selected_ip['ip']);
        $response->setOption(255, false);

        $this->logger->info("Sending offer", array(
            'mac' => $this->packet->getChaddr(),
            'ip' => $response->getYiaddr()
        ));

        return $response;
    }
}