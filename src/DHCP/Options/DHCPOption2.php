<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption2 - time offset
 *
 * @package DHCP\Options
 */
class DHCPOption2 extends DHCPOption
{

    /**
     * Option number = 2.
     */
    const OPTION = 2;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Time Offset';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    private $offset = [];

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = null, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setOffset($data);
        }
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * @return array
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param array $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareToSend()
    {
        return array_merge(array(self::OPTION, self::$length), $this->offset);
    }
}