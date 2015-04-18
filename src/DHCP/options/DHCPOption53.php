<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption53 extends DHCPOption {

    const OPTION = 53;

    protected static $name = 'DHCP Message Type';
    protected static $length = 1;

    protected $type;

    const MSG_DHCPDISCOVER = 1;
    const MSG_DHCPOFFER = 2;
    const MSG_DHCPREQUEST = 3;
    const MSG_DHCPDECLINE = 4;
    const MSG_DHCPACK = 5;
    const MSG_DHCPNAK = 6;
    const MSG_DHCPRELEASE = 7;
    const MSG_DHCPINFORM = 8;
    const MSG_UNUSED = 9;

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->type = array_shift($details);
        }
    }

    public function getType(){
        return $this->type;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function prepareToSend(){
        return array(self::OPTION, self::$length, $this->type);
    }

}