<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption119 - Domain Search
 *
 * @package DHCP\Options
 */
class DHCPOption119 extends DHCPOption
{

    /**
     * Option number = 119.
     */
    const OPTION = 119;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Domain Search';

}