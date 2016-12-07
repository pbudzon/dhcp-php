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
    const OPTION = 15;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Domain Name';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

}