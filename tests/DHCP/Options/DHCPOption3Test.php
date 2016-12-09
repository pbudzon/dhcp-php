<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption3;

class DHCPOption3Test extends DHCPOptionTest
{
    public function testConstructErrorLength()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Length for option DHCP\Options\DHCPOption3 must be at least 4, got 2');
        $option = new DHCPOption3(2);
    }

    public function testConstructErrorDataLength()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length of option details for DHCP\Options\DHCPOption3 must be at least 4');
        $option = new DHCPOption3(4, []);
    }
}
