<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption51 - IP Address Lease time
 *
 * @package DHCP\Options
 */
class DHCPOption51 extends DHCPOption
{
    /**
     * Option number = 51.
     */
    protected static $option = 51;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'IP Address Lease Time';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    public function setTime($time)
    {
        $this->data = array_map("ord", str_split(pack("N", $time)));
    }

    public function getTime()
    {
        $time = unpack("N", implode("", array_map("chr", $this->data)));

        return array_shift($time);
    }
}
