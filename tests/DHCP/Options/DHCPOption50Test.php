<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption50;

class DHCPOption50Test extends DHCPOptionTest
{
    public function testSetBroadcast()
    {
        $option = new DHCPOption50();
        $option->setIp("1.2.3.4");
        $this->assertEquals("1.2.3.4", $option->getIp());
        $this->assertEquals([1, 2, 3, 4], $option->getData());
        $this->assertEquals([50, 4, 1, 2, 3, 4], $option->prepareToSend());
    }
}

