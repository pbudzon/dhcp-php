<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption28 - Broadcast address
 *
 * @package DHCP\Options
 */
class DHCPOption28 extends DHCPOption
{
    /**
     * Option number = 28.
     */
    protected static $option = 28;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Broadcast Address';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    public function setBroadcast($ipAddress)
    {
        $this->data = explode(".", $ipAddress);
    }

    public function getBroadcast()
    {
        return implode(".", $this->data);
    }
}
