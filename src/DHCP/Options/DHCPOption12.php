<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption12 - Hostname
 *
 * @package DHCP\Options
 */
class DHCPOption12 extends DHCPOption
{
    /**
     * Option number = 12.
     */
    protected static $option = 12;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Hostname';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

    public function setHostname($hostname)
    {
        $this->data = array_map("ord", str_split($hostname));
    }

    public function getHostname()
    {
        return implode("", array_map('chr', $this->data));
    }
}
