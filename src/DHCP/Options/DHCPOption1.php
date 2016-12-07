<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption1 - Subnet Mask
 *
 * @package DHCP\Options
 */
class DHCPOption1 extends DHCPOption
{

    /**
     * Option number = 1.
     */
    const OPTION = 1;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Subnet Mask';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    /**
     * @var array
     */
    private $mask = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = null, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setMask($data);
        }
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * @return array
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @param array $mask
     */
    public function setMask($mask)
    {
        $this->mask = $mask;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend()
    {
        return array_merge(array(self::OPTION, self::$length), $this->mask);
    }

}