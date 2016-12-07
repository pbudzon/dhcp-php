<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption50 - Requested IP address
 *
 * @package DHCP\Options
 */
class DHCPOption50 extends DHCPOption
{

    /**
     * Option number = 50.
     */
    const OPTION = 50;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Requested IP Address';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    private $ip = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setIp($data);
        }
    }


    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * Returns IP address in X.X.X.X format.
     *
     * @return string
     */
    public function getIp()
    {
        return implode(".", $this->ip);
    }

    /**
     * @param array|bool $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend()
    {
        return array_merge(array(self::OPTION, self::$length), $this->ip);
    }
}