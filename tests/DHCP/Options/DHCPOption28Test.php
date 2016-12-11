<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption28;

class DHCPOption28Test extends DHCPOptionTest
{
    public function testSetBroadcast()
    {
        $option = new DHCPOption28();
        $option->setBroadcast("255.255.255.255");
        $this->assertEquals("255.255.255.255", $option->getBroadcast());
        $this->assertEquals([255, 255, 255, 255], $option->getData());
        $this->assertEquals([28, 4, 255, 255, 255, 255], $option->prepareToSend());
    }
}
