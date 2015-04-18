<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption55 extends DHCPOption {

    const OPTION = 55;

    protected static $name = 'Parameter Request List';
    protected static $minLength = 1;

    private $parameters;

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);

        if($details){
            foreach($details as $option){
                $className = 'DHCP\Options\DHCPOption'.$option;
                if(class_exists($className)){
                    $this->parameters[] = new $className(null, false, $logger);
                }
                else{
                    $logger->warning("Option 55: ignoring option $option");
                }
            }
        }
    }

}