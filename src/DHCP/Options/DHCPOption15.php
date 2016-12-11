<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption15 - Domain name
 *
 * @package DHCP\Options
 */
class DHCPOption15 extends DHCPOption
{
    /**
     * Option number = 15.
     */
    protected static $option = 15;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Domain Name';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

    public function setDomainName($domainName)
    {
        $this->data = array_map("ord", str_split($domainName));
    }

    public function getDomainName()
    {
        return implode("", array_map('chr', $this->data));
    }
}
