<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption2 - time offset
 * @package DHCP\Options
 */
class DHCPOption2 extends DHCPOption {

    /**
     * Option number = 2.
     */
    const OPTION = 2;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Time Offset';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

}