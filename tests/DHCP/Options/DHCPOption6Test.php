<?php

namespace DHCP\Tests\Options;

use DHCP\Options\DHCPOption6;

class DHCPOption6Test extends DHCPOptionTest
{
    public function testSetDataFromList()
    {
        $option = new DHCPOption6();
        $option->setDataFromList(['1.2.3.4', '6.6.6.6', '9.9.9.9']);
        $this->assertEquals([1, 2, 3, 4, 6, 6, 6, 6, 9, 9, 9, 9], $option->getData());
        $this->assertEquals([6, 12, 1, 2, 3, 4, 6, 6, 6, 6, 9, 9, 9, 9], $option->prepareToSend());
    }
}
