<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption53;

class DHCPOption53Test extends DHCPOptionTest
{
    public function testConstruct()
    {
        $option = new DHCPOption53();
        $this->assertEmpty($option->getData());

        $option = new DHCPOption53(1, [DHCPOption53::MSG_DHCPREQUEST]);
        $this->assertEquals([3], $option->getData());
        $this->assertEquals(3, $option->getType());
    }

    public function testGetAndSetType()
    {
        $option = new DHCPOption53(1, [3]);
        $option->setType(DHCPOption53::MSG_DHCPACK);
        $this->assertEquals([5], $option->getData());
        $this->assertEquals(5, $option->getType());
        $this->assertEquals([53, 1, 5], $option->prepareToSend());
    }
}
