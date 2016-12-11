<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption55 - Parameter Request List
 *
 * @package DHCP\Options
 */
class DHCPOption55 extends DHCPOption
{

    /**
     * Option number = 55.
     */
    protected static $option = 55;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Parameter Request List';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = array(), LoggerInterface $logger = null)
    {
        parent::__construct($length, $data, $logger);

        if ($data) {
            $this->data = [];
            foreach ($data as $option) {
                $className = 'DHCP\Options\DHCPOption'.$option;
                if (class_exists($className)) {
                    $this->data[] = new $className(null, [], $logger);
                } elseif ($this->logger) {
                    $logger->warning("Option 55: ignoring option $option");
                }
            }
        }
    }

    public function prepareToSend()
    {
        $data = array();

        foreach ($this->data as $option) {
            $data = array_merge($data, $option->prepareToSend());
        }

        return $data;
    }
}
