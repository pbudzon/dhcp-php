<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption61 - Client ID
 *
 * @package DHCP\Options
 */
class DHCPOption61 extends DHCPOption
{

    /**
     * Option number = 61.
     */
    protected static $option = 61;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Client-identifier';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 2;

    private $type;

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = array(), LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);

        $data = array_map('dechex', $data);
        $this->type = array_shift($data);
        $this->data = implode(":", $data);
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
