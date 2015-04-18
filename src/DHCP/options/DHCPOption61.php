<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption61 extends DHCPOption {

    const OPTION = 61;

    protected static $name = 'Client-identifier';
    protected static $minLength = 2;

    private $type;
    private $id;

    public function __construct($length = null, $details = false, LoggerInterface $logger = null){
        parent::__construct($length, $details, $logger);

        $details = array_map('dechex', $details);
        $this->type = array_shift($details);
        $this->id = implode(":", $details);
    }

}