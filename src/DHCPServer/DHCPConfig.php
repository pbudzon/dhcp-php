<?php
namespace DHCPServer;

class DHCPConfig
{

    private $ip;
    private $mask;
    private $network;
    private $broadcast;
    private $router;
    private $dns;
    private $lease_time = 300;

    public function __construct($ip = false, $dns = false)
    {
        if ($ip) {
            $this->createConfig($ip, $dns);
        }
    }

    /**
     * @return mixed
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @return mixed
     */
    public function getNetwork()
    {
        return $this->network;
    }

    /**
     * @return mixed
     */
    public function getBroadcast()
    {
        return $this->broadcast;
    }

    /**
     * @return mixed
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @return mixed
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
        return $this->lease_time;
    }


    private function createConfig($ip, $dns)
    {
        $ip = explode("/", $ip);
        if (count($ip) != 2) {
            throw new \InvalidArgumentException("Wrong ip provided, should be in format x.x.x.x/x");
        }

        $this->ip = array_shift($ip);
        $cidr = array_shift($ip);

        $this->network = $this->network($cidr);
        $this->mask = $this->cidr2Mask($cidr);
        $this->broadcast = $this->broadcast($cidr);
        $this->router = $this->ip;

        if ($dns) {
            $this->dns = $dns;
        } else {
            $this->dns = array($this->router);
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
        return long2ip((ip2long($this->ip)) & ((-1 << (32 - (int)$cidr))));
    }

    public static function mask2cidr($mask)
    {
        $long = ip2long($mask);
        $base = ip2long('255.255.255.255');

        return 32 - log(($long ^ $base) + 1, 2);
    }
}
