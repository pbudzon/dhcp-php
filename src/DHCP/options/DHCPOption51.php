<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption51 extends DHCPOption {

    const OPTION = 51;

    protected static $name = 'IP Address Lease Time';
    protected static $length = 4;

    protected $time = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->setTime(array_shift($details));
        }
    }

    public function getTime(){
        return $this->time;
    }

    public function setTime($time){
        $this->time = array_map("ord", str_split(pack("N", $time)));
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, self::$length), $this->time);
    }

}