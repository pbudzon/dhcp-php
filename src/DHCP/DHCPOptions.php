<?php
namespace DHCP;

use Psr\Log\LoggerInterface;

/**
 * Class DHCPOptions
 *
 * This is a list representation of the options in the packet.
 *
 * @package DHCP
 */
class DHCPOptions implements \Iterator{

    /**
     * @var \DHCP\Options\DHCPOption[] List of options
     */
    protected $options = array();

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int Internal key of the iterator.
     */
    private $key = 0;

    /**
     * Creates a list of options from given data from packet or an empty list if nothing passed.
     *
     * @param mixed $data
     * @param LoggerInterface $logger
     * @todo Make this pretty
     */
    public function __construct($data = false, LoggerInterface $logger = null){
        $this->logger = $logger;

        if(!$data) return;

        $currentOption = false;
        $currentLength = 0;
        $currentPosition = 1;
        $currentDetails = array();
        for($i = 1;; $i++){
            if(isset($data["options$i"])){

                if($currentOption){
                    if(!$currentLength){
                        $currentLength = $data["options$i"];
                        if($currentLength == 0){
                            $this->addOption($currentOption);
                            $currentOption = false;
                            $currentLength = 0;
                            $currentPosition = 1;
                            $currentDetails = array();
                        }
                    }
                    else{
                        if($currentPosition <= $currentLength){
                            $currentDetails[] = $data["options$i"];

                            if($currentPosition == $currentLength){
                                $this->addOption($currentOption, $currentLength, $currentDetails);

                                $currentOption = false;
                                $currentLength = 0;
                                $currentPosition = 1;
                                $currentDetails = array();

                            }
                            else{
                                $currentPosition++;
                            }
                        }
                    }
                }
                else{
                    $currentOption = $data["options$i"];
                }
            }
            else{
                break;
            }
        }
    }

    /**
     * Creates a representation of each option that can be then passed to pack() to create the response.
     *
     * @return array
     */
    public function prepareToSend(){
        $data = array();

        foreach($this->options as $option){
            $data = array_merge($data, $option->prepareToSend());
        }

        return $data;
    }

    /**
     * Adds an option with given data to the list.
     * This should be used to add an option from the socket data; not manually creating an option.
     * To manually add a new/replace existing option, use DHCPPacket\
     *
     * @param int $option Option number. If it's not defined in \DHCP\Options\DHCPOptionX, it will be skipped.
     * @param int $length Length of the data (number of octets).
     * @param mixed $details Data of the option (optional).
     */
    private function addOption($option, $length = 0, $details = null){
        if($option == 255 || $option == 0) return; //prevent manual adding of End and Pad

        $className = 'DHCP\Options\DHCPOption'.$option;
        if(class_exists($className)){
            $this->options[] = new $className($length, $details, $this->logger);
        }
        elseif($this->logger){
            $this->logger->notice("Ignoring option {op}", array('op' => $option));
        }
    }

    /**
     * Replace existing (or add new if doesn't exist) option with a new object.
     * @param int $option Option number.
     * @param Options\DHCPOption $newObject New option object.
     */
    public function replaceOption($option, $newObject){
        foreach($this->options as $k => $op){
            if(get_class($op) == 'DHCP\Options\DHCPOption'.$option){
                $this->options[$k] = $newObject;
                return;
            }
        }

        //no object found, add new
        $this->options[] = $newObject;
    }

    /**
     * Looks for specific option and returns it, if found.
     * @param int $option Option number.
     * @return mixed Option (Options\DHCPOption) or null if not found.
     */
    public function getOption($option){
        foreach($this->options as $op){
            if(get_class($op) == 'DHCP\Options\DHCPOption'.$option){
                return $op;
            }
        }
    }

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return Options\DHCPOption Option.
     */
    public function current()
    {
        if($this->valid()){
            return $this->options[$this->key];
        }
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return isset($this->options[$this->key]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->key = 0;
    }


}