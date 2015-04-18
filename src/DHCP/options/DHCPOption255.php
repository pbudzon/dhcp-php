<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;


class DHCPOption255 extends DHCPOption {

    const OPTION = 255;

    protected static $name = 'End';
    protected static $length = 1;


    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
    }

    public function prepareToSend(){
        return array(self::OPTION);
    }

}