<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption3 - Router
 *
 * @package DHCP\Options
 */
class DHCPOption3 extends DHCPOption
{

    /**
     * Option number = 3.
     */
    const OPTION = 3;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Router';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 4;

    private $router = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);
        if ($data) {
            $this->setRouter($data);
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
        return array_merge(array(self::OPTION, count($this->router)), $this->router);
    }

    /**
     * @return array|bool
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param array|bool $router
     */
    public function setRouter($router)
    {
        $this->router = $router;
    }

}