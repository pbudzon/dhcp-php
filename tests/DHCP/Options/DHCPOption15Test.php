<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption15;

class DHCPOption15Test extends DHCPOptionTest
{
    public function testSetHostname()
    {
        $option = new DHCPOption15();
        $option->setDomainName("example.com");
        $this->assertEquals("example.com", $option->getDomainName());
        $this->assertEquals(
            [101, 120, 97, 109, 112, 108, 101, 46, 99, 111, 109],
            $option->getData()
        );
        $this->assertEquals(
            [15, 11, 101, 120, 97, 109, 112, 108, 101, 46, 99, 111, 109],
            $option->prepareToSend()
        );
    }
}
