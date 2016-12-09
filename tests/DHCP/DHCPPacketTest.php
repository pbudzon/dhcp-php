<?php

namespace DHCP\Tests;

use DHCP\DHCPPacket;
use DHCP\Options\DHCPOption53;

class DHCPPacketTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructForDiscover()
    {
        $data = file_get_contents(__DIR__.'/../resources/dhcpdiscover', FILE_BINARY);

        $packet = new DHCPPacket($data);
        $this->assertEquals(DHCPOption53::MSG_DHCPDISCOVER, $packet->getType());

        $this->markTestIncomplete();

    }

    public function testConstructForRequest()
    {
        $data = file_get_contents(__DIR__.'/../resources/dhcprequest', FILE_BINARY);

        $packet = new DHCPPacket($data);
        $this->assertEquals(DHCPOption53::MSG_DHCPREQUEST, $packet->getType());

        $this->markTestIncomplete();
//        var_dump($packet->getOptions());
    }
}
