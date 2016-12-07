<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption121 - Classless route
 * @package DHCP\Options
 */
class DHCPOption121 extends DHCPOption {

    /**
     * Option number = 121.
     */
    const OPTION = 121;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Classless Route';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 5;

}