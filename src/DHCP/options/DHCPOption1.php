<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;


class DHCPOption1 extends DHCPOption {

    const OPTION = 1;

    protected static $name = 'Subnet Mask';
    protected static $length = 4;

    private $mask = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->mask = $details;
        }
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, self::$length), $this->mask);
    }

}