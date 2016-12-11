<?php
namespace DHCPServer;

class DHCPConfig
{
    /**
     * @var string
     */
    private $ipAddress;
    /**
     * @var string
     */
    private $mask;
    /**
     * @var string
     */
    private $network;
    /**
     * @var string
     */
    private $broadcast;
    /**
     * @var string
     */
    private $router;
    /**
     * @var array
     */
    private $dns = [];
    /**
     * Default lease time, in seconds
     *
     * @var int
     */
    private $leaseTime = 300;
    /**
     * @var string
     */
    private $dbPassword;

    public function __construct($ipAddress = null, $dbPassword = null, $dns = null)
    {
        if ($ipAddress) {
            $this->createConfig($ipAddress, $dns);
        }
        $this->dbPassword = $dbPassword;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * @return string
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @return string
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @return string
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @return string
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return array
     */
    public function getDns()
    {
        return $this->dns;
    }

    /**
     * @return int
     */
    public function getLeaseTime()
    {
        return $this->leaseTime;
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    private function createConfig($ipAddress, $dns)
    {
        $ipAddress = explode("/", $ipAddress);
        if (count($ipAddress) != 2) {
            throw new \InvalidArgumentException("Wrong ip provided, should be in format x.x.x.x/x");
        }

        $this->ipAddress = array_shift($ipAddress);
        $cidr = array_shift($ipAddress);

        $this->network = $this->network($cidr);
        $this->mask = $this->cidr2Mask($cidr);
        $this->broadcast = $this->broadcast($cidr);
        $this->router = $this->ipAddress;

        $this->dns = array($this->router);
        if ($dns) {
            $this->dns = $dns;
        }
    }

    private function cidr2Mask($bitcount)
    {
        $netmask = str_split(str_pad(str_pad('', $bitcount, '1'), 32, '0'), 8);
        foreach ($netmask as &$element) {
            $element = bindec($element);
        }

        return join('.', $netmask);
    }

    private function broadcast($cidr)
    {
        return long2ip(ip2long($this->network) + pow(2, (32 - (int)$cidr)) - 1);
    }

    private function network($cidr)
    {
        return long2ip((ip2long($this->ipAddress)) & ((-1 << (32 - (int)$cidr))));
    }

    public static function mask2cidr($mask)
    {
        $long = ip2long($mask);
        $base = ip2long('255.255.255.255');

        return 32 - log(($long ^ $base) + 1, 2);
    }
}
