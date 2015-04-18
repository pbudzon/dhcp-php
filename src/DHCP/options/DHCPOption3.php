<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption3 extends DHCPOption {

    const OPTION = 3;

    protected static $name = 'Router';
    protected static $minLength = 4;

    private $router = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->router = $details;
        }
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, count($this->router)), $this->router);
    }

}