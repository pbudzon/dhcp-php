<?php
namespace DHCP;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPPacket
 *
 * This class provides an object representation of any DHCP packet.
 * Refer to [RFC2131](https://www.ietf.org/rfc/rfc2132.txt) for detailed description of a standard DHCP packet.
 *
 * This can be used in two ways: you can pass binary representation of the packet directly from the socket to
 * get all the details about the packet itself. Or you can create this object and set all the values yourself.
 *
 * Example for listening for incoming packets:
 * ```
 * $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP); //create udp socket
 * socket_bind($socket, '0.0.0.0', 67); //listen on port 67 (DHCP server port)
 * while(true) {
 *      $buffer = socket_read($socket , 576); //reading incoming packet
 *      if($buffer){
 *           $packet = new DHCPPacket($buffer);
 *      }
 * }
 * ```
 *
 * @package DHCP
 * @see     https://www.ietf.org/rfc/rfc2132.txt RFC2131 Dynamic Host Configuration Protocol
 */
class DHCPPacket
{

    /**
     * Packet type: request from client
     */
    const OP_BOOTREQUEST = 1;
    /**
     * Packet type: response from server
     */
    const OP_BOOTREPLY = 2;

    /**
     * Format to unpack the packet
     */
    const UNPACK_FORMAT = "Cop/Chtype/Chlen/Chops/Nxid/nsecs/nflags/Nciaddr/Nyiaddr/Nsiaddr/Ngiaddr/C16chaddr/C64sname/C128file/C4cookie/C*options";
    /**
     * Format to pack the packet
     */
    const PACK_FORMAT = "CCCCNnnNNNNC16C64C128C4C*";

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int Message type. One of OP_ constants.
     */
    protected $op;
    /**
     * @var int Hardware address type, see ARP section in "Assigned Numbers" RFC; e.g., '1' = 10mb ethernet.
     */
    protected $htype;
    /**
     * @var int Hardware address length (e.g. '6' for 10mb ethernet).
     */
    protected $hlen;
    /**
     * @var int Client sets to zero, optionally used by relay agents when booting via a relay agent.
     */
    protected $hops;
    /**
     * @var int Transaction ID, a random number chosen by the client,
     * used by the client and server to associate messages and responses between a client and a server.
     */
    protected $xid;
    /**
     * @var int Filled in by client, seconds elapsed since client began address acquisition or renewal process.
     */
    protected $secs;
    /**
     * @var int Flags. Currently can only be 0 or 1 (for broadcast messages).
     */
    protected $flags;
    /**
     * @var int  Client IP address; only filled in if client is in BOUND, RENEW or REBINDING state and can respond to
     *      ARP request
     */
    protected $ciaddr;
    /**
     * @var int Client IP address.
     */
    protected $yiaddr;
    /**
     * @var int IP address of next server to use in bootstrap; returned in DHCPOFFER, DHCPACK by server.
     */
    protected $siaddr;
    /**
     * @var int Relay agent IP address, used in booting via a relay agent.
     */
    protected $giaddr;
    /**
     * @var string Client hardware address (MAC address).
     */
    protected $chaddr;
    /**
     * @var string Optional server host name.
     */
    protected $sname;
    /**
     * @var string Boot file name, null terminated string;
     * "generic" name or null in DHCPDISCOVER, fully qualified directory-path name in DHCPOFFER.
     */
    protected $file;
    /**
     * @var array Magic DHCP Cookie extracted from options.
     * The first four octets of the 'options' field of the DHCP message contain the (decimal) values 99, 130, 83 and
     * 99, respectively (this is the same magic cookie as is defined in RFC 1497
     */
    protected $magiccookie;
    /**
     * @var DHCPOptions The actual options passed.
     */
    protected $options;

    /**
     * Creates a new DHCPPacket object.
     *
     * @param mixed           $packet Pass binary data from network socket to be processed.
     * @param LoggerInterface $logger
     */
    public function __construct($packet = false, LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->logger = $logger;
        }
        if ($packet) {

            $data = unpack(self::UNPACK_FORMAT, $packet);

            if ($data) {
                $this->op = $data['op'];
                $this->htype = $data['htype'];
                $this->hlen = $data['hlen'];
                $this->hops = $data['hops'];
                $this->xid = $data['xid'];
                $this->secs = $data['secs'];
                $this->flags = $data['flags'];
                $this->ciaddr = $data['ciaddr'];
                $this->yiaddr = $data['yiaddr'];
                $this->siaddr = $data['siaddr'];
                $this->giaddr = $data['giaddr'];

                $this->chaddr = implode(":", $this->convertToHex($data, "chaddr", $this->hlen));
                $this->sname = implode("", $this->convertToHex($data, "sname", 64, true));
                $this->file = implode("", $this->convertToHex($data, 'file', 128, true));

                for ($i = 1; $i <= 4; $i++) {
                    $this->magiccookie[] = $data['cookie'.$i];

                }

                $this->options = new DHCPOptions($data, $logger);
            }
        } else {
            $this->options = new DHCPOptions(false, $logger);
        }
    }

    /**
     * Creates binary representation of the packet that can be send through a socket to a DHCP Server/Client.
     *
     * Example:
     * ```
     * $data = $packet->pack();
     * socket_sendto($socket, $data, strlen($data), 0, '255.255.255.255', 68);
     * ```
     *
     * @return mixed
     */
    public function pack()
    {
        $parameters = array(
            self::PACK_FORMAT,
            $this->op,
            $this->htype,
            $this->hlen,
            $this->hops,
            $this->xid,
            $this->secs,
            $this->flags,
            $this->ciaddr,
            $this->yiaddr,
            $this->siaddr,
            $this->giaddr
        );

        $chaddr = explode(":", $this->chaddr);
        foreach ($chaddr as $v) {
            $parameters[] = hexdec($v);
        }

        $parameters += array_fill(count($parameters), 16 - $this->hlen, 0);

        //sname
        $parameters += array_fill(count($parameters), 64, 0);

        //file
        $parameters += array_fill(count($parameters), 128, 0);

        //cookie + options
        $parameters = array_merge($parameters, $this->magiccookie, $this->options->prepareToSend());

        $data = call_user_func_array('pack', $parameters);

        return $data;
    }

    /**
     * Finds option describing type of the packet (DISCOVER, OFFER, etc) and returns the type if found.
     *
     * @return int One of the Options\DHCPOption53 MSG_ constants or null if type was not found.
     */
    public function getType()
    {
        /**
         * @var $typeOption Options\DHCPOption53
         */
        $typeOption = $this->options->getOption(53);
        if ($typeOption) {
            return $typeOption->getType();
        }
    }

    /**
     * Find and change or create a new option with the type of the packet.
     *
     * @param int $type One of the Options\DHCPOption53 MSG_ constants.
     */
    public function setType($type)
    {
        $typeOption = new Options\DHCPOption53(1, array($type), $this->logger);
        $this->options->replaceOption(53, $typeOption);
    }

    /**
     * Sets any option to specified value.
     *
     * @param int   $option Option number. Option must be defined in Options\DHCPOptionX where X is option number.
     * @param mixed $value  value to set the option to.
     */
    public function setOption($option, $value)
    {
        $className = 'DHCP\Options\DHCPOption'.$option;
        if (!is_array($value)) {
            $value = array($value);
        }
        $newOption = new $className(count($value), $value, $this->logger);
        $this->options->replaceOption($option, $newOption);
    }

    /**
     * Returns $op
     *
     * @return int
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * Sets $op to given value. Must be one of the OP_ constants.
     *
     * @param int $op
     */
    public function setOp($op)
    {
        $this->op = $op;
    }

    /**
     * Returns $htype
     *
     * @return int
     */
    public function getHtype()
    {
        return $this->htype;
    }

    /**
     * Sets $htype to given value.
     *
     * @param int $htype
     */
    public function setHtype($htype)
    {
        $this->htype = $htype;
    }

    /**
     * Returns $hlen
     *
     * @return int
     */
    public function getHlen()
    {
        return $this->hlen;
    }

    /**
     * Sets $hlen to given value.
     *
     * @param int $hlen
     */
    public function setHlen($hlen)
    {
        $this->hlen = $hlen;
    }

    /**
     * Returns $hops.
     *
     * @return int
     */
    public function getHops()
    {
        return $this->hops;
    }

    /**
     * Sets $hops to given value.
     *
     * @param int $hops
     */
    public function setHops($hops)
    {
        $this->hops = $hops;
    }

    /**
     * Returns $xid.
     *
     * @return int
     */
    public function getXid()
    {
        return $this->xid;
    }

    /**
     * Sets $xid to given value.
     *
     * @param int $xid
     */
    public function setXid($xid)
    {
        $this->xid = $xid;
    }

    /**
     * Returns $secs.
     *
     * @return int
     */
    public function getSecs()
    {
        return $this->secs;
    }

    /**
     * Sets $secs to given value.
     *
     * @param int $secs
     */
    public function setSecs($secs)
    {
        $this->secs = $secs;
    }

    /**
     * Returns $flags.
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Sets flags to given value.
     * Pass 1 to set BROADCAST flag.
     *
     * @param int $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * Returns $ciaddr in X.X.X.X format.
     *
     * @return string
     */
    public function getCiaddr()
    {
        return long2ip($this->ciaddr);
    }

    /**
     * Sets $ciaddr to given value.
     * Accepts ip address as X.X.X.X or as long (int).
     *
     * @param mixed $ciaddr
     */
    public function setCiaddr($ciaddr)
    {
        if (strpos($ciaddr, ".") !== false) {
            $ciaddr = ip2long($ciaddr);
        }
        $this->ciaddr = $ciaddr;
    }

    /**
     * Returns $yiaddr in X.X.X.X format.
     *
     * @return string
     */
    public function getYiaddr()
    {
        return long2ip($this->yiaddr);
    }

    /**
     * Sets $yiaddr to given value.
     * Accepts ip address as X.X.X.X or as long (int).
     *
     * @param mixed $yiaddr
     */
    public function setYiaddr($yiaddr)
    {
        if (strpos($yiaddr, ".") !== false) {
            $yiaddr = ip2long($yiaddr);
        }
        $this->yiaddr = $yiaddr;
    }

    /**
     * Returns $siaddr;
     *
     * @return int
     */
    public function getSiaddr()
    {
        return $this->siaddr;
    }

    /**
     * Sets $siaddr to given value.
     *
     * @param int $siaddr
     */
    public function setSiaddr($siaddr)
    {
        $this->siaddr = $siaddr;
    }

    /**
     * Returns $giaddr.
     *
     * @return int
     */
    public function getGiaddr()
    {
        return $this->giaddr;
    }

    /**
     * Sets $giaddr to given value.
     *
     * @param int $giaddr
     */
    public function setGiaddr($giaddr)
    {
        $this->giaddr = $giaddr;
    }

    /**
     * Returns $chaddr.
     *
     * @return string
     */
    public function getChaddr()
    {
        return $this->chaddr;
    }

    /**
     * Sets $chaddr to given value.
     *
     * @param string $chaddr
     */
    public function setChaddr($chaddr)
    {
        $this->chaddr = $chaddr;
    }

    /**
     * Returns $sname;
     *
     * @return string
     */
    public function getSname()
    {
        return $this->sname;
    }

    /**
     * Sets $sname to given value.
     *
     * @param string $sname
     */
    public function setSname($sname)
    {
        $this->sname = $sname;
    }

    /**
     * Returns $file.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Sets $file to given value.
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * Returns $magiccookie.
     *
     * @return string
     */
    public function getMagiccookie()
    {
        return $this->magiccookie;
    }

    /**
     * Sets $magiccookie to given value.
     * This value should always be array(99, 130, 83, 99) according to RFC.
     *
     * @param string $magiccookie
     */
    public function setMagiccookie($magiccookie)
    {
        $this->magiccookie = $magiccookie;
    }

    /**
     * List of options in the packet.
     *
     * @return DHCPOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Replace the current list of options with given one.
     *
     * @param DHCPOptions $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }


    private function convertToHex($data, $keyName, $length, $breakOnEmpty = false)
    {
        $converted = array();
        for ($i = 1; $i <= $length; $i++) {
            $hex = dechex($data[$keyName.$i]);
            if ($breakOnEmpty && $hex == 0) {
                break;
            }
            $converted[] = $hex;
        }

        return $converted;
    }

}