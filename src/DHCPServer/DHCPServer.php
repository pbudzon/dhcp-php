<?php
namespace DHCPServer;

use DHCP\DHCPPacket;
use DHCP\Options\DHCPOption53;
use Monolog\Processor\MemoryUsageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DHCPServer extends Command {

    private $inputSocket;
    private $outputSocket;
    /**
     * @var DHCPConfig
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Start DHCP server')
            ->addArgument(
                'ip',
                InputArgument::REQUIRED,
                'IP address to bind DHCP server to with mask. Example: 10.0.0.1/25'
            )
            ->addArgument(
                'dns',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'DNS (separate multiple names with a space)'
            )
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

        $this->config = new DHCPConfig($input->getArgument('ip'), $input->getArgument('dns'));
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->inputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $this->outputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($this->outputSocket, SOL_SOCKET, SO_BROADCAST, 1);

        $ip = $this->config->getIp();
        if(socket_bind($this->inputSocket, '0.0.0.0', 67)
            && socket_bind($this->outputSocket, $ip, 68)) {
            $this->logger->info("Bind on 0.0.0.0:67 and $ip:68...");

            while(true) {
                $this->logger->debug("Waiting for data on 0.0.0.0:67...");
                $buffer = socket_read($this->inputSocket , 576);
                if (!$buffer) {
                    $this->reportSocketReadError($this->inputSocket);
                } else {
                    $this->logger->debug("Parsing packet");
                    $packet = new DHCPPacket($buffer, $this->logger);
                    $this->sendResponse($this->getResponse($packet, $ip));
                }
            }
        }
        else{
            $this->logger->emergency("Cannot bind to 0.0.0.0:67 or $ip:68");
        }
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
                break;
        }

        return $response;
    }

    private function sendResponse(DHCPPacket $response){
        if($response){
            $data = $response->pack();
            $sent = socket_sendto($this->outputSocket, $data, strlen($data), 0, '255.255.255.255', 68);
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