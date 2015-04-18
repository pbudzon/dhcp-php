<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption2 extends DHCPOption {

    const OPTION = 2;

    protected static $name = 'Time Offset';
    protected static $length = 4;


}