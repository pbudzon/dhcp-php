<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption61;

class DHCPOption61Test extends DHCPOptionTest
{
    public function testConstructAndGetter()
    {
        $option = new DHCPOption61();
        $this->assertEmpty($option->getData());

        $option = new DHCPOption61(7, [1, 0, 160, 36, 171, 251, 156]);
        $this->assertEquals([1, 0, 160, 36, 171, 251, 156], $option->getData());
        $this->assertEquals(1, $option->getType());
        $this->assertEquals("0:a0:24:ab:fb:9c", $option->getClientId());
    }

    public function testSetterAndGetter()
    {
        $option = new DHCPOption61();
        $option->setType("1");
        $option->setClientId("0:a0:24:ab:fb:9c");
        $this->assertEquals(1, $option->getType());
        $this->assertEquals("0:a0:24:ab:fb:9c", $option->getClientId());
        $this->assertEquals([1, 0, 160, 36, 171, 251, 156], $option->getData());
    }
}
