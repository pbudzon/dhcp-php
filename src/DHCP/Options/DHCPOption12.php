<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption12 - Hostname
 * @package DHCP\Options
 */
class DHCPOption12 extends DHCPOption {

    /**
     * Option number = 12.
     */
    const OPTION = 12;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Hostname';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

    private $hostname;

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);

        if($details){
            $this->hostname = implode("", array_map('chr', $details));
        }
    }

}