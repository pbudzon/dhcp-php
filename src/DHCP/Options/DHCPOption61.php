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

    /**
     * @return mixed
     */
    public function getType()
    {
        $data = $this->data;
        $type = array_shift($data);

        return dechex($type);
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->data[0] = hexdec($type);
    }

    /**
     * todo: work for type=0 (non-mac client id)
     * @return string
     */
    public function getClientId()
    {
        $data = $this->data;
        array_shift($data); //get rid of the type
        $data = array_map('dechex', $data);

        return implode(":", $data);
    }

    /**
     * todo: work for type=0 (non-mac client id)
     * @param $clientId
     */
    public function setClientId($clientId)
    {
        $clientId = array_map('hexdec', explode(":", $clientId));
        if (isset($this->data[0])) { //preserve type
            $type = $this->data[0];
            $this->data = [$type];
        }
        $this->data = array_merge($this->data, $clientId);
    }
}
