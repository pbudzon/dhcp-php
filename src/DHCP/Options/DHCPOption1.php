<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption1 - Subnet Mask
 *
 * @package DHCP\Options
 */
class DHCPOption1 extends DHCPOption
{
    /**
     * Option number = 1.
     */
    protected static $option = 1;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Subnet Mask';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    public function setMask($mask)
    {
        $this->data = explode(".", $mask);
    }
}
