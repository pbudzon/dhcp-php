<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption1;

class DHCPOption1Test extends DHCPOptionTest
{
    public function testConstruct()
    {
        $option = new DHCPOption1();
        $this->assertEmpty($option->getMask());

        $option = new DHCPOption1(4, ['255', '255', '255', '255']);
        $this->assertEquals(['255', '255', '255', '255'], $option->getMask());
    }


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

    public function testSetMask()
    {
        $option = new DHCPOption1();
        $option->setMask(['255', '255', '255', '255']);
        $this->assertEquals(['255', '255', '255', '255'], $option->getMask());
    }

    public function testPrepareToSend()
    {
        $option = new DHCPOption1();
        $this->assertEquals([DHCPOption1::OPTION, 4], $option->prepareToSend());
        $option->setMask(['255', '255', '255', '255']);
        $this->assertEquals([DHCPOption1::OPTION, 4, '255', '255', '255', '255'], $option->prepareToSend());
    }
}
