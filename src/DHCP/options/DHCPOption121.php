<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

class DHCPOption121 extends DHCPOption {

    const OPTION = 121;

    protected static $name = 'Classless Route';
    protected static $minLength = 5;


}