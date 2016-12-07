<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption12 - Hostname
 * @package DHCP\Options
 */
class DHCPOption12 extends DHCPOption {

    /**
     * Option number = 12.
     */
    const OPTION = 12;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Hostname';
    /**
     * {@inheritdoc}
     */
    protected static $minLength = 1;

    private $hostname;

    /**
     * {@inheritdoc}
     */
    public function __construct($length = null, $data = false, LoggerInterface $logger = null){
        parent::__construct($length, $data, $logger);

        if($data){
            $this->setHostname(implode("", array_map('chr', $data)));
        }
    }

    protected function validate($length, $data)
    {
        parent::validate($length, $data);
    }

    /**
     * @return string
     */
    public function getHostname()
    {
        return $this->hostname;
    }

    /**
     * @param string $hostname
     */
    public function setHostname($hostname)
    {
        $this->hostname = $hostname;
    }

}