<?php

use Slim\App as Slim;
use Kodelines\Middleware\ApiMiddleware;
use Tuupola\Middleware\JwtAuthentication;
use Slim\Psr7\Response;
use Kodelines\Oauth\Server;
use Kodelines\Oauth\Client;
use Kodelines\Tools\Domain;

return function (Slim $app) {

    //$app->addErrorMiddleware(dev(),dev(),dev());

    $app->add(new JwtAuthentication([
        "secret" => Client::getKids(),
        "secure" => Domain::isSecure(),
        "rules" => [
            new Tuupola\Middleware\JwtAuthentication\RequestPathRule([
                "path" => [
                    "/"
                ],
                "ignore" => [
                    "/oauth/token",
                    "/test",
                    "/store",
                    "/config",
                    "/products"
                ]
            ]),
            new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
                "ignore" => ["OPTIONS"],
            ])
        ],
        "before" => function ($request, $arguments)  {
           
            try {

                //Istanzio nuovo oauth server con le variabili del token
                new Server($request, $arguments);


            } catch (Throwable $e) { 

                $response = new Response();

                return $response->withStatus($e->getCode());
  
            }
            
        },
        "error" => function ($response, $arguments) {
         
            return $response->withStatus(401);
   
        }
    ]));


    $app->add(new ApiMiddleware($app->getContainer()));

    $app->addBodyParsingMiddleware();

};