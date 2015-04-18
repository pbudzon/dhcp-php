<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption54 extends DHCPOption {

    const OPTION = 54;

    protected static $name = 'Server Identifier';
    protected static $length = 4;

    private $server = array();

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->server = $details;
        }
    }

    public function prepareToSend(){
        return array_merge(array(self::OPTION, self::$length), $this->server);
    }

}