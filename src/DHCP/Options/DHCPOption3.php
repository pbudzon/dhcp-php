<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption3 - Router
 * @package DHCP\Options
 */
class DHCPOption3 extends DHCPOption {

    /**
     * Option number = 3.
     */
    const OPTION = 3;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Router';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 4;

    private $router = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);
        if($details){
            $this->router = $details;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend(){
        return array_merge(array(self::OPTION, count($this->router)), $this->router);
    }

}