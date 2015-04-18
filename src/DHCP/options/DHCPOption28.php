<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption28 extends DHCPOption {

    const OPTION = 28;

    protected static $name = 'Broadcast Address';
    protected static $length = 4;

    private $address = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->address = $details;
        }
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, self::$length), $this->address);
    }

}