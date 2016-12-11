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
    protected static $option = 3;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Router';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 4;

    public function setRouter($router)
    {
        $this->data = explode(".", $router);
    }

    public function getRouter()
    {
        return implode(".", $this->data);
    }
}
