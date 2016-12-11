<?php
namespace DHCP\Options;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOption57 - Maximum DHCP Message Size
 *
 * @package DHCP\Options
 */
class DHCPOption57 extends DHCPOption
{
    /**
     * Option number = 57.
     */
    protected static $option = 57;
    /**
     * {@inheritdoc}
     */
    protected static $name = 'Maximum DHCP Message Size';
    /**
     * {@inheritdoc}
     */
    protected static $length = 2;

    public function setSize($size)
    {
        $this->data = array_map("ord", str_split(pack("n", $size)));
    }

    public function getSize()
    {
        $size = unpack("n", implode("", array_map("chr", $this->data)));

        return array_shift($size);
    }
}
