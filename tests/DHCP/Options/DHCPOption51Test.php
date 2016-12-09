<?php

namespace DHCP\Tests\Options;


use DHCP\Options\DHCPOption51;

class DHCPOption51Test extends DHCPOptionTest
{
    public function testSetTime()
    {
        $this->markTestIncomplete();
        $option = new DHCPOption51();
        $this->assertEmpty($option->getData());

        $option->setTime(300);
//        var_dump($option->getData());
    }

}
