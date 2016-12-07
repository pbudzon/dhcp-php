<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption54 - Server ID
 *
 * @package DHCP\Options
 */
class DHCPOption54 extends DHCPOption
{

    /**
     * Option number = 54.
     */
    const OPTION = 54;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Server Identifier';
    /**
     * {@inheritdoc}
     */
    protected static $length = 4;

    private $server = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setServer($data);
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
        return array_merge(array(self::OPTION, self::$length), $this->server);
    }

    /**
     * @return array|bool
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param array|bool $server
     */
    public function setServer($server)
    {
        $this->server = $server;
    }
}