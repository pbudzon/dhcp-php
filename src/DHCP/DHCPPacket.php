<?php
namespace DHCP;

use Psr\Log\LoggerInterface;

class DHCPPacket {

    const OP_BOOTREQUEST = 1;
    const OP_BOOTREPLY = 2;

    const UNPACK_FORMAT = "Cop/Chtype/Chlen/Chops/Nxid/nsecs/nflags/Nciaddr/Nyiaddr/Nsiaddr/Ngiaddr/C16chaddr/C64sname/C128file/C4cookie/C*options";
    const PACK_FORMAT = "CCCCNnnNNNNC16C64C128C4C*";

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $op;
    protected $htype;
    protected $hlen;
    protected $hops;
    protected $xid;
    protected $secs;
    protected $flags;
    protected $ciaddr;
    protected $yiaddr;
    protected $siaddr;
    protected $giaddr;
    protected $chaddr;
    protected $sname;
    protected $file;
    protected $magiccookie;
    protected $options;

    public function __construct($packet = false, LoggerInterface $logger = null){
        if($packet) {

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

                for($i = 1; $i <= 4; $i++){
                    $this->magiccookie[] = $data['cookie'.$i];

                }

                $this->options = new DHCPOptions($data, $logger);
            }
        }
        else{
            $this->options = new DHCPOptions(false, $logger);
        }
    }

    public function pack(){
        $parameters = array(self::PACK_FORMAT, $this->op, $this->htype, $this->hlen, $this->hops, $this->xid, $this->secs,
            $this->flags, $this->ciaddr, $this->yiaddr, $this->siaddr, $this->giaddr);

        $chaddr = explode(":", $this->chaddr);
        foreach($chaddr as $v){
            $parameters[] = hexdec($v);
        }

        $parameters += array_fill(count($parameters), 16-$this->hlen, 0);

        //sname
        $parameters += array_fill(count($parameters), 64, 0);

        //file
        $parameters += array_fill(count($parameters), 128, 0);

        //cookie + options
        $parameters = array_merge($parameters, $this->magiccookie, $this->options->prepareToSend());

        $data = call_user_func_array('pack', $parameters);

        return $data;
    }

    public function getType(){
        $typeOption = $this->options->getOption(53);
        if($typeOption){
            return $typeOption->getType();
        }
    }

    public function setType($type){
        $typeOption = new Options\DHCPOption53(1, array($type), $this->logger);
        $this->options->replaceOption(53, $typeOption);
    }

    public function setOption($option, $value){
        $className = 'DHCP\Options\DHCPOption'.$option;
        if(!is_array($value)) $value = array($value);
        $newOption = new $className(null, $value, $this->logger);
        $this->options->replaceOption($option, $newOption);
    }

    /**
     * @return mixed
     */
    public function getOp()
    {
        return $this->op;
    }

    /**
     * @param mixed $op
     */
    public function setOp($op)
    {
        $this->op = $op;
    }

    /**
     * @return mixed
     */
    public function getHtype()
    {
        return $this->htype;
    }

    /**
     * @param mixed $htype
     */
    public function setHtype($htype)
    {
        $this->htype = $htype;
    }

    /**
     * @return mixed
     */
    public function getHlen()
    {
        return $this->hlen;
    }

    /**
     * @param mixed $hlen
     */
    public function setHlen($hlen)
    {
        $this->hlen = $hlen;
    }

    /**
     * @return mixed
     */
    public function getHops()
    {
        return $this->hops;
    }

    /**
     * @param mixed $hops
     */
    public function setHops($hops)
    {
        $this->hops = $hops;
    }

    /**
     * @return mixed
     */
    public function getXid()
    {
        return $this->xid;
    }

    /**
     * @param mixed $xid
     */
    public function setXid($xid)
    {
        $this->xid = $xid;
    }

    /**
     * @return mixed
     */
    public function getSecs()
    {
        return $this->secs;
    }

    /**
     * @param mixed $secs
     */
    public function setSecs($secs)
    {
        $this->secs = $secs;
    }

    /**
     * @return mixed
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * @param mixed $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }

    /**
     * @return mixed
     */
    public function getCiaddr()
    {
        return long2ip($this->ciaddr);
    }

    /**
     * Accepts ip address as X.X.X.X or as long
     * @param mixed $ciaddr
     */
    public function setCiaddr($ciaddr)
    {
        if(strpos($ciaddr, ".") !== false){
            $ciaddr = ip2long($ciaddr);
        }
        $this->ciaddr = $ciaddr;
    }

    /**
     * @return mixed
     */
    public function getYiaddr()
    {
        return long2ip($this->yiaddr);
    }

    /**
     * Accepts ip address as X.X.X.X or as long
     * @param mixed $yiaddr
     */
    public function setYiaddr($yiaddr)
    {
        if(strpos($yiaddr, ".") !== false){
            $yiaddr = ip2long($yiaddr);
        }
        $this->yiaddr = $yiaddr;
    }

    /**
     * @return mixed
     */
    public function getSiaddr()
    {
        return $this->siaddr;
    }

    /**
     * @param mixed $siaddr
     */
    public function setSiaddr($siaddr)
    {
        $this->siaddr = $siaddr;
    }

    /**
     * @return mixed
     */
    public function getGiaddr()
    {
        return $this->giaddr;
    }

    /**
     * @param mixed $giaddr
     */
    public function setGiaddr($giaddr)
    {
        $this->giaddr = $giaddr;
    }

    /**
     * @return string
     */
    public function getChaddr()
    {
        return $this->chaddr;
    }

    /**
     * @param string $chaddr
     */
    public function setChaddr($chaddr)
    {
        $this->chaddr = $chaddr;
    }

    /**
     * @return string
     */
    public function getSname()
    {
        return $this->sname;
    }

    /**
     * @param string $sname
     */
    public function setSname($sname)
    {
        $this->sname = $sname;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return string
     */
    public function getMagiccookie()
    {
        return $this->magiccookie;
    }

    /**
     * @param string $magiccookie
     */
    public function setMagiccookie($magiccookie)
    {
        $this->magiccookie = $magiccookie;
    }

    /**
     * @return DHCPOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param DHCPOptions $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }


    private function convertToHex($data, $keyName, $length, $breakOnEmpty = false){
        $converted = array();
        for($i = 1; $i <= $length; $i++){
            $hex = dechex($data[$keyName.$i]);
            if($breakOnEmpty && $hex == 0) break;
            $converted[] = $hex;
        }
        return $converted;
    }

}