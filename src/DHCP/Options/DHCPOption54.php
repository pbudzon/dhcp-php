<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption54 - Server ID
 *
 * @package DHCP\Options
 */
class DHCPOption54 extends DHCPOption
{
    /**
     * Option number = 54.
     */
    protected static $option = 54;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Server Identifier';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    public function setIdentifier($dhcp)
    {
        $this->data = explode(".", $dhcp);
    }

    public function getIdentifier()
    {
        return implode(".", $this->data);
    }
}
