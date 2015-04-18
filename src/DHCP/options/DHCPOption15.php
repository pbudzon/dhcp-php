<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption15 extends DHCPOption {

    const OPTION = 15;

    protected static $name = 'Domain Name';
    protected static $minLength = 1;


}