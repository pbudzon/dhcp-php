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
//
//    public function __construct($length = null, $data = array(), LoggerInterface $logger = null)
//    {
//        parent::__construct($length, $data, $logger);
//
//        if ($data) {
//            $this->setHostname($data);
////            $this->setHostname(implode("", array_map('chr', $data)));
//        }
}
