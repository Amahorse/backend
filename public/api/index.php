<?php

namespace Kodelines;

use DI\ContainerBuilder;
use Slim\App;

//Require autoloader
require_once '../../vendor/autoload.php';

System::start();

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions( __DIR__ . '/config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

return $container->get(App::class)->run();

?>