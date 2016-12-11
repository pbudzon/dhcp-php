<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption54;

class DHCPOption54Test extends DHCPOptionTest
{
    public function testGetAndSetIdentifier()
    {
        $option = new DHCPOption54();
        $option->setIdentifier("10.20.30.40");
        $this->assertEquals([10, 20, 30, 40], $option->getData());
        $this->assertEquals("10.20.30.40", $option->getIdentifier());
    }
}
