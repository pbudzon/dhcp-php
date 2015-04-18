<?php
require __DIR__.'/../../vendor/autoload.php';

use Symfony\Component\Console\Application;

$console = new Application();

$console->add(new \DHCPServer\DHCPServer());
$console->run();