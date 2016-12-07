<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption2;


class DHCPOption2Test extends DHCPOptionTest
{

    public function testConstruct()
    {
        $option = new DHCPOption2();
        $this->assertEmpty($option->getOffset());

        $option = new DHCPOption2(4, ['1', '2', '3', '4']);
        $this->assertEquals(['1', '2', '3', '4'], $option->getOffset());
    }

    public function testConstructErrorLength()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Length for option DHCP\Options\DHCPOption2 must be 4, got 2');
        $option = new DHCPOption2(2);
    }

    public function testConstructErrorDataLength()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Length of option details for DHCP\Options\DHCPOption2 must be 4');
        $option = new DHCPOption2(4, []);
    }

    public function testSetMask()
    {
        $option = new DHCPOption2();
        $option->setOffset(['1', '2', '3', '4']);
        $this->assertEquals(['1', '2', '3', '4'], $option->getOffset());
    }

    public function testPrepareToSend()
    {
        $option = new DHCPOption2();
        $this->assertEquals([DHCPOption2::OPTION, 4], $option->prepareToSend());
        $option->setOffset(['1', '2', '3', '4']);
        $this->assertEquals([DHCPOption2::OPTION, 4, '1', '2', '3', '4'], $option->prepareToSend());
    }
}
