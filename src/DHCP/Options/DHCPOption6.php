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
    const OPTION = 6;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'DNS';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 4;

    private $dns = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            if (count($data) < 3) { //assume array with 1-3 ip addresses
                $ips = array();
                foreach ($data as $ip) {
                    $ips = array_merge($ips, explode(".", $ip));
                }
                $data = $ips;
            }

            $this->setDns($data);
        }
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend()
    {
        return array_merge(array(self::OPTION, count($this->dns)), $this->dns);
    }

    /**
     * @return array
     */
    public function getDns()
    {
        return $this->dns;
    }

    /**
     * @param array $dns
     */
    public function setDns($dns)
    {
        $this->dns = $dns;


    }
}