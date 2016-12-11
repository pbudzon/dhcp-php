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

    public function testPrepareToSend()
    {
        $option = $option = new DHCPOption3();
        $this->assertEquals([3, 0], $option->prepareToSend());
        $option->setData(['1', '2', '3', '4']);
        $this->assertEquals([3, 4, '1', '2', '3', '4'], $option->prepareToSend());
    }

    public function testSetAndGetMask()
    {
        $option = new DHCPOption3();
        $this->assertEquals('', $option->getRouter());
        $option->setRouter('1.2.3.4');
        $this->assertEquals('1.2.3.4', $option->getRouter());
        $this->assertEquals(['1', '2', '3', '4'], $option->getData());
    }

    public function testGetOption()
    {
        $this->assertEquals(3, DHCPOption3::getOption());
    }
}
