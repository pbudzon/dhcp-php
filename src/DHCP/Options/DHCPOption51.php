<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption51 - IP Address Lease time
 *
 * @package DHCP\Options
 */
class DHCPOption51 extends DHCPOption
{

    /**
     * Option number = 51.
     */
    const OPTION = 51;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'IP Address Lease Time';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    protected $time = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setTime(array_shift($data));
        }
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setTime($time)
    {
        $this->time = array_map("ord", str_split(pack("N", $time)));
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend()
    {
        return array_merge(array(self::OPTION, self::$length), $this->time);
    }
}