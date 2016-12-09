<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption55;

class DHCPOption55Test extends DHCPOptionTest
{
    public function testConstruct()
    {
        $option = new DHCPOption55();
        $this->assertEmpty($option->getData());

        $option = new DHCPOption55(1, [50]);
        $this->assertArrayHasKey(0, $option->getData());
        $this->assertInstanceOf('DHCP\Options\DHCPOption50', $option->getData()[0]);
    }

    public function testConstructErrorLength()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Length for option DHCP\Options\DHCPOption55 must be at least 1, got 0');
        $option = new DHCPOption55(0);
    }

    public function testConstructErrorDataLength()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length of option details for DHCP\Options\DHCPOption55 must be at least 1');
        $option = new DHCPOption55(4, []);
    }
}
