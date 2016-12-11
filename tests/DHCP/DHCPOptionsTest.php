<?php

namespace DHCP\Tests;

use DHCP\DHCPOptions;
use DHCP\Options\DHCPOption53;
use DHCP\Options\DHCPOption6;

class DHCPOptionsTest extends \PHPUnit_Framework_TestCase
{
    private $data;

    public function setUp()
    {
        $this->data = unserialize(file_get_contents(__DIR__.'/../resources/dhcpdiscover.serialized'));
    }

    public function testConstruct()
    {
        // should not fail
        $options = new DHCPOptions();
        $options = new DHCPOptions($this->data);
    }

    public function testIterator()
    {
        $options = new DHCPOptions($this->data);
        $this->assertCount(3, $options);
        foreach ($options as $option) {
            $this->assertNotEmpty($option);
        }
    }

    public function testGetOption()
    {
        $options = new DHCPOptions($this->data);
        $opt = $options->getOption(53);
        $this->assertInstanceOf(DHCPOption53::class, $opt);

        $opt = $options->getOption(999);
        $this->assertNull($opt);
    }

    public function testReplaceOption()
    {
        $options = new DHCPOptions($this->data);
        $opt = $options->getOption(53);
        $newOpt = new DHCPOption53();
        $newOpt->setType(4);
        $options->replaceOption($newOpt);

        $replacedOpt = $options->getOption(53);
        $this->assertInstanceOf(DHCPOption53::class, $replacedOpt);
        $this->assertEquals($replacedOpt, $newOpt);
        $this->assertNotEquals($replacedOpt, $opt);
        $this->assertCount(3, $options);

        $notPresentOpt = new DHCPOption6();
        $options->replaceOption($notPresentOpt);
        $this->assertCount(4, $options);
    }

    public function testPrepareToSend()
    {
        $options = new DHCPOptions();
        $this->assertEquals([], $options->prepareToSend());

        $options = new DHCPOptions($this->data);
        $this->assertEquals(
            [53, 1, 1, 50, 4, 10, 0, 0, 2, 1, 0, 28, 0, 15, 0, 6, 0, 12, 0, 119, 0, 3, 0],
            $options->prepareToSend()
        );
    }
}
