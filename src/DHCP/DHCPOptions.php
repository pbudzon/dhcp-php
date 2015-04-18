<?php
namespace DHCP;

use Psr\Log\LoggerInterface;

class DHCPOptions implements \Iterator{

    protected $options = array();

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $key = 0;

    /**
     * @param bool $data
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

    public function prepareToSend(){
        $data = array();

        foreach($this->options as $option){
            $data = array_merge($data, $option->prepareToSend());
        }

        return $data;
    }

    private function addOption($option, $length = 0, $details = null){
        if($option == 255 || $option == 0) return;

        $className = 'DHCP\Options\DHCPOption'.$option;
        if(class_exists($className)){
            $this->options[] = new $className($length, $details, $this->logger);

        }
        elseif($this->logger){
            $this->logger->notice("Ignoring option {op}", array('op' => $option));
        }
    }

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

    public function getOption($option){
        foreach($this->options as $op){
            if(get_class($op) == 'DHCP\Options\DHCPOption'.$option){
                return $op;
            }
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        if($this->valid()){
            return $this->options[$this->key];
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        $this->key++;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
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
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->key = 0;
    }


}