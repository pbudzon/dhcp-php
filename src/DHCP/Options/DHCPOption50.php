<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption50 - Requested IP address
 *
 * @package DHCP\Options
 */
class DHCPOption50 extends DHCPOption
{
    /**
     * Option number = 50.
     */
    protected static $option = 50;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Requested IP Address';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    public function getIp()
    {
        return implode(".", $this->data);
    }

    /**
     * Set IP using a regular IP format ("4.4.4.4") and not packet data.
     *
     * @param string $ipAddress IP address, like "1.2.3.4"
     */
    public function setIp($ipAddress)
    {
        $this->data = explode(".", $ipAddress);
    }
}
