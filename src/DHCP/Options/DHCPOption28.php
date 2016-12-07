<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption28 - Broadcast address
 *
 * @package DHCP\Options
 */
class DHCPOption28 extends DHCPOption
{

    /**
     * Option number = 28.
     */
    const OPTION = 28;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Broadcast Address';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    private $address = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setAddress($data);
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
        return array_merge(array(self::OPTION, self::$length), $this->address);
    }

    /**
     * @return array|bool
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param array|bool $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }


}