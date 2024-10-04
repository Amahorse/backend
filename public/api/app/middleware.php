<?php

use Slim\App as Slim;
use Kodelines\Middleware\ApiMiddleware;
use Tuupola\Middleware\JwtAuthentication;
use Kodelines\Oauth\Server;
use Kodelines\Oauth\Client;
use Kodelines\Tools\Domain;

return function (Slim $app) {
    //TODO: capire esattament cosa fa e ripristinare
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
                    "/oauth/token"
                ]
            ]),
            new Tuupola\Middleware\JwtAuthentication\RequestMethodRule([
                "ignore" => ["OPTIONS"],
            ])
        ],
        "before" => function ($request, $arguments)  {
   
            //Istanzio nuovo oauth server con le variabili del token
            new Server($request, $arguments);

        },
        "error" => function ($response, $arguments) {
            
            //TODO: gestire errore token scaduto
            return $response->withStatus(401);
   
        }
    ]));


    $app->add(new ApiMiddleware($app->getContainer()));

    $app->addBodyParsingMiddleware();

};