<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption255 - END
 *
 * @package DHCP\Options
 */
class DHCPOption255 extends DHCPOption
{

    /**
     * Option number = 255.
     */
    protected static $option = 255;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'End';
    /**
     * {@inheritdoc}
     */
    protected static $length = 1;
}
