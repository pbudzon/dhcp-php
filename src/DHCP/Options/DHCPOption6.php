<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption6 - DNS
 *
 * @package DHCP\Options
 */
class DHCPOption6 extends DHCPOption
{

    /**
     * Option number = 6.
     */
    protected static $option = 6;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'DNS';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 4;

    /**
     * Set list of DNS servers by passing a list of IP addresses instead of packet data.
     *
     * @param array $list List of IPs, for example ["10.10.20.10", "4.4.4.4", "6.6.6.6"]
     */
    public function setDataFromList($list = array())
    {
        $this->data = array();
        foreach ($list as $ip) {
            $this->data = array_merge($this->data, explode(".", $ip));
        }
    }
}
