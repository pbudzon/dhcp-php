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
     * If $length is provided, the basic details about the data from the packet will be checked.
     * Parameters to the constructor should only be passed when creating the option from socket data.
     * If you want to create an option manually, don't pass anything here, use specific set() methods to
     * modify the option's values.
     *
     * @param mixed           $length Number of octets that the data was taking in the packet.
     * @param mixed           $data   Data from the packet.
     * @param LoggerInterface $logger
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        $this->logger = $logger;

        if (!is_null($length)) {
            $this->validate($length, $data);
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
                "Length for option ".get_called_class()." must be at least ".static::$length.", got $length"
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
     * Creates a representation of the option that can be passed to pack() to send a packet.
     * This method should be overwritten in each option.
     *
     * @return array
     */
    public function prepareToSend()
    {
        return array();
    }
}