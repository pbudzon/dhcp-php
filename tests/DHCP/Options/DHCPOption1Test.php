<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption1;

class DHCPOption1Test extends DHCPOptionTest
{
    public function testConstructErrorLength()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Length for option DHCP\Options\DHCPOption1 must be 4, got 2');
        $option = new DHCPOption1(2);
    }

    public function testConstructErrorDataLength()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length of option details for DHCP\Options\DHCPOption1 must be 4');
        $option = new DHCPOption1(4, []);
    }

    public function testPrepareToSend()
    {
        $option = $option = new DHCPOption1();
        $this->assertEquals([1, 0], $option->prepareToSend());
        $option->setData(['255', '255', '255', '255']);
        $this->assertEquals([1, 4, '255', '255', '255', '255'], $option->prepareToSend());
    }

    public function testSetAndGetMask()
    {
        $option = new DHCPOption1();
        $this->assertEquals('', $option->getMask());
        $option->setMask('255.255.255.255');
        $this->assertEquals('255.255.255.255', $option->getMask());
        $this->assertEquals(['255', '255', '255', '255'], $option->getData());
    }

    public function testGetOption()
    {
        $this->assertEquals(1, DHCPOption1::getOption());
    }
}
