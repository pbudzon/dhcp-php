<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption57;

class DHCPOption57Test extends DHCPOptionTest
{
    public function testConstructAndGetter()
    {
        $option = new DHCPOption57(2, [5, 220]);
        $this->assertEquals(1500, $option->getSize());
    }

    public function testSetterAndGetter()
    {
        $option = new DHCPOption57();
        $option->setSize(1500);
        $this->assertEquals(1500, $option->getSize());
        $this->assertEquals([5, 220], $option->getData());
    }
}
