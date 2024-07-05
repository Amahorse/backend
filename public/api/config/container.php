<?php

use Psr\Container\ContainerInterface;
use Slim\App as Slim;
use Slim\Factory\AppFactory;

return [

    // Application settings
    'settings' => [
        'addContentLengthHeader' => false,
        'determineRouteBeforeAppMiddleware' => true
    ],

    Slim::class => function (ContainerInterface $container) {

        $app = AppFactory::createFromContainer($container);

        if(config('app','cache') == true && !dev()) {

            $routeCollector = $app->getRouteCollector();
        
            $routeCollector->setCacheFile(_DIR_CACHE_ . 'api.routes.php');

        }      

        // Register routes
        (require __DIR__ . '/routes.php')($app);

        // Register middleware
        (require  __DIR__ . '/middleware.php')($app);


        return $app;
    }
    
];