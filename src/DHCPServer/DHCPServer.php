<?php
namespace DHCPServer;

use DHCP\DHCPPacket;
use DHCP\Options\DHCPOption53;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DHCPServer extends Command {

    private $inputSockets = array();
    private $outputSockets = array();
    private $config = array();

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Start DHCP server')
//            ->addArgument(
//                'ip',
//                InputArgument::REQUIRED,
//                'IP address to bind DHCP server to'
//            )
                ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $handler = new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::DEBUG);

        if ($output->isQuiet()) {
            $handler->setLevel(\Monolog\Logger::ERROR);
        }
        elseif ($output->isVerbose()) {
            $handler->setLevel(\Monolog\Logger::NOTICE);
        }
        elseif ($output->isVeryVerbose()) {
            $handler->setLevel(\Monolog\Logger::INFO);
        }

        $handler->setFormatter(new \Bramus\Monolog\Formatter\ColoredLineFormatter());
        $handler->pushProcessor(new MemoryUsageProcessor());
        $this->logger = new \Monolog\Logger('dhcp');
        $this->logger->pushHandler($handler);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $database = new Postgresql();
        $config = $database->getServerConfig();

        $this->logger->debug("Reading and applying configuation");
        foreach($config as $server_config){
            $inputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            $outputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
            socket_set_option($outputSocket, SOL_SOCKET, SO_BROADCAST, 1);

            if(socket_bind($inputSocket, "0.0.0.0", 67)
                && socket_bind($outputSocket, $server_config['server_ip'], 68)) {
                $this->logger->info("Bind on {$server_config['server_ip']}:67 and {$server_config['server_ip']}:68...");

                $this->inputSockets[$server_config['id']] = $inputSocket;
                $this->outputSockets[$server_config['id']] = $outputSocket;
                $this->config[$server_config['id']] = $server_config;
            }
            else{
                $this->logger->emergency("Cannot bind to ip {$server_config['server_ip']}");
            }
        }
        $this->logger->debug("Sockets created, ready to listen");

        while(true){
            $read = array_values($this->inputSockets);
            $except = null;

            $num_changed_sockets = socket_select($read, $except, $except, null);
            if ($num_changed_sockets === false) {
                $this->logger->emergency("Socket_select error: ".socket_last_error());
            } else if ($num_changed_sockets > 0) {

                foreach($read as $read_socket){
                    $config_key = array_search($read_socket, $this->inputSockets);
                    if($config_key === false){
                        $this->logger->emergency("Could not find config for socket!");
                        continue;
                    }

                    $buffer = socket_read($read_socket , 576);
                    if (!$buffer) {
                        $this->reportSocketReadError($read_socket);
                    } else {
                        $this->logger->debug("Parsing packet");
                        $packet = new DHCPPacket($buffer, $this->logger);
                        $this->sendResponse($this->getResponse($packet, $this->config[$config_key]), $config_key);
                    }
                }
            }
        }

//        if(socket_bind($this->inputSocket, '0.0.0.0', 67)
//            && socket_bind($this->outputSocket, $ip, 68)) {
//            $this->logger->info("Bind on 0.0.0.0:67 and $ip:68...");
//
//            while(true) {
//                $this->logger->debug("Waiting for data on 0.0.0.0:67...");
//                $buffer = socket_read($this->inputSocket , 576);
//                if (!$buffer) {
//                    $this->reportSocketReadError();
//                } else {
//                    $this->logger->debug("Parsing packet");
//                    $packet = new DHCPPacket($buffer, $this->logger);
//                    $this->sendResponse($this->getResponse($packet, $ip));
//                }
//            }
//        }
//        else{
//            $this->logger->emergency("Cannot bind to 0.0.0.0:67 or $ip:68");
//        }
    }

    private function getResponse(DHCPPacket $packet, $dhcp_config){
        $response = false;
        switch($packet->getType()){
            case DHCPOption53::MSG_DHCPDISCOVER:
                $this->logger->debug("DHCP Discover received");
                $response = (new Response\DHCPOffer($packet, $this->logger))->respond($dhcp_config);
                break;

            case DHCPOption53::MSG_DHCPREQUEST:
                $this->logger->debug("DHCP Request received");
                $response = (new Response\DHCPAck($packet, $this->logger))->respond($dhcp_config);
                break;

            case DHCPOption53::MSG_DHCPRELEASE:
                $this->logger->debug("DHCP Release received");
                //todo: ack to db
                break;
        }

        return $response;
    }

    private function sendResponse(DHCPPacket $response, $socket_id){
        if($response){
            $data = $response->pack();
            $sent = socket_sendto($this->outputSockets[$socket_id], $data, strlen($data), 0, '255.255.255.255', 68);
            if($sent){
                $this->logger->debug("Sent response, $sent bytes");
            }
            else{
                $this->logger->error("Response not sent");
            }
        }
        else{
            $this->logger->warning("Nothing to send");
        }
    }

    private function reportSocketReadError($socket){
        $error = socket_last_error($socket);
        if ($error) {
            socket_clear_error($socket);
            $this->logger->error("Error when receiving data from socket: " . $error);
        } else {
            $this->logger->warning("No data from socket received!");
        }
    }
}