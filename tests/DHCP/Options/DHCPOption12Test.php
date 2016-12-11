<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption12;

class DHCPOption12Test extends DHCPOptionTest
{
    public function testSetHostname()
    {
        $option = new DHCPOption12();
        $option->setHostname("host.example.com");
        $this->assertEquals("host.example.com", $option->getHostname());
        $this->assertEquals(
            [104, 111, 115, 116, 46, 101, 120, 97, 109, 112, 108, 101, 46, 99, 111, 109],
            $option->getData()
        );
        $this->assertEquals(
            [12, 16, 104, 111, 115, 116, 46, 101, 120, 97, 109, 112, 108, 101, 46, 99, 111, 109],
            $option->prepareToSend()
        );
    }
}
