<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption6 extends DHCPOption {

    const OPTION = 6;

    protected static $name = 'DNS';
    protected static $minLength = 4;

    private $dns = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            if(count($details) < 3){ //assume array with 1-3 ip addresses
                $ips = array();
                foreach($details as $ip){
                    $ips = array_merge($ips, explode(".", $ip));
                }
                $details = $ips;
            }
            $this->dns = $details;
        }
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, count($this->dns)), $this->dns);
    }

}