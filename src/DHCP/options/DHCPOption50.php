<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption50 extends DHCPOption {

    const OPTION = 50;

    protected static $name = 'Requested IP Address';
    protected static $length = 4;

    private $ip = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->ip = $details;
        }
    }

    public function getIp(){
        return implode(".", $this->ip);
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, self::$length), $this->ip);
    }

}