<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption51;

class DHCPOption51Test extends DHCPOptionTest
{
    public function testSetTime()
    {
        $option = new DHCPOption51();
        $option->setTime(300);
        $this->assertEquals([0, 0, 1, 44], $option->getData());
        $this->assertEquals(300, $option->getTime());
    }

    public function testConstructing()
    {
        $option = new DHCPOption51(4, [0, 0, 0, 100]);
        $this->assertEquals([0, 0, 0, 100], $option->getData());
        $this->assertEquals(100, $option->getTime());
    }
}
