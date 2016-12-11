<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption53 - DHCP Message type
 *
 * @package DHCP\Options
 */
class DHCPOption53 extends DHCPOption
{
    /**
     * Option number = 53.
     */
    protected static $option = 53;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'DHCP Message Type';
    /**
     * {@inheritdoc}
     */
    protected static $length = 1;

    const MSG_DHCPDISCOVER = 1;
    const MSG_DHCPOFFER = 2;
    const MSG_DHCPREQUEST = 3;
    const MSG_DHCPDECLINE = 4;
    const MSG_DHCPACK = 5;
    const MSG_DHCPNAK = 6;
    const MSG_DHCPRELEASE = 7;
    const MSG_DHCPINFORM = 8;
    const MSG_UNUSED = 9;

    /**
     * @return int One to the MSG_xxx consts from this object.
     */
    public function getType()
    {
        return $this->data[0];
    }

    /**
     * @param int $type One to the MSG_xxx consts from this object.
     */
    public function setType($type)
    {
        $this->data = [$type];
    }
}
