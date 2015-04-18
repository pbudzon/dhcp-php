<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

abstract class DHCPOption {

    protected static $name;
    protected static $length = 0;
    protected static $minLength = 0;
    protected $logger;

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        $this->logger = $logger;

        if(is_null($length)) return;

        $class = get_called_class();
        if($class::$length && $length != $class::$length){
            throw new \UnexpectedValueException("Length for option $class must be {$class::$length}, got $length");
        }

        if($class::$minLength && $length < $class::$minLength){
            throw new \UnexpectedValueException("Length for option $class must be at least {$class::$length}, got $length");
        }

        if($class::$length && count($details) != $class::$length){
            throw new \InvalidArgumentException("Length of option details for $class must be {$class::$length}");
        }

        if($class::$minLength && count($details) < $class::$minLength){
            throw new \InvalidArgumentException("Length of option details for $class must be at least {$class::$minLength}");
        }
    }

    public function prepareToSend(){
        return array();
    }
}