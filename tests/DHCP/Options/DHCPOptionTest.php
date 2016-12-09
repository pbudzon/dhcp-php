<?php

namespace DHCP\Tests\Options;


class DHCPOptionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        /** @var \DHCP\Options\DHCPOption $option */
        $option = $this->getMockForAbstractClass('DHCP\Options\DHCPOption');
        $this->assertEmpty($option->getData());

        $option = $this->getMockForAbstractClass('DHCP\Options\DHCPOption', [4, ['255', '255', '255', '255']]);
        $this->assertEquals(['255', '255', '255', '255'], $option->getData());
    }

    public function testSetter()
    {
        /** @var \DHCP\Options\DHCPOption $option */
        $option = $this->getMockForAbstractClass('DHCP\Options\DHCPOption');
        $option->setData(['255', '255', '255', '255']);
        $this->assertEquals(['255', '255', '255', '255'], $option->getData());
    }

    public function testPrepareToSend()
    {
        /** @var \DHCP\Options\DHCPOption $option */
        $option = $this->getMockForAbstractClass('DHCP\Options\DHCPOption');
        $this->assertEquals([null, 0], $option->prepareToSend());
        $option->setData(['255', '255', '255', '255']);
        $this->assertEquals([null, 4, '255', '255', '255', '255'], $option->prepareToSend());
    }

}
