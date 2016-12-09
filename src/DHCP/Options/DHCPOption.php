<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Abstract class DHCPOption
 *
 * @package DHCP\Options
 */
abstract class DHCPOption
{

    protected static $option;
    /**
     * @var string Name of the option.
     */
    protected static $name;
    /**
     * @var int Number of octets the option should have in the packet.
     * Options should either have this or $minLength, not both.
     */
    protected static $length = 0;
    /**
     * @var int Minimal number of octets the option should have in the packet.
     * Options should either have this or $length, not both.
     */
    protected static $minLength = 0;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Data inside the packet.
     *
     * @var array
     */
    protected $data = [];

    /**
     * If $length is provided, the basic details about the data from the packet will be checked.
     *
     * @param mixed           $length Number of octets that the data was taking in the packet.
     * @param mixed           $data   Data from the packet.
     * @param LoggerInterface $logger
     */
    public function __construct($length = null, $data = array(), LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        if (!is_null($length)) {
            $this->validate($length, $data);

            if ($data) {
                $this->data = $data;
            }
        }
    }

    protected function validate($length, $data)
    {
        if (static::$length && $length != static::$length) {
            throw new \UnexpectedValueException(
                "Length for option ".get_called_class()." must be ".static::$length.", got $length"
            );
        }

        if (static::$minLength && $length < static::$minLength) {
            throw new \UnexpectedValueException(
                "Length for option ".get_called_class()." must be at least ".static::$minLength.", got $length"
            );
        }

        if (static::$length && count($data) != static::$length) {
            throw new \InvalidArgumentException(
                "Length of option details for ".get_called_class()." must be ".static::$length
            );
        }

        if (static::$minLength && count($data) < static::$minLength) {
            throw new \InvalidArgumentException(
                "Length of option details for ".get_called_class()." must be at least ".static::$minLength
            );
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public static function getOption()
    {
        return static::$option;
    }

    /**
     * Creates a representation of the option that can be passed to pack() to send a packet.
     *
     * @return array
     */
    public function prepareToSend()
    {
        return array_merge(array(static::$option, count($this->data)), $this->data);
    }
}
