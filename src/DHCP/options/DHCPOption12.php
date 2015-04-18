<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption12 extends DHCPOption {

    const OPTION = 12;

    protected static $name = 'Hostname';
    protected static $minLength = 1;

    private $hostname;

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);

        if($details){
            $this->hostname = implode("", array_map('chr', $details));
        }
    }

}