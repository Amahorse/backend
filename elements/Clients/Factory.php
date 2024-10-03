<?php

declare(strict_types=1);

namespace Elements\Clients;

use Slim\Routing\RouteCollectorProxy;
use Kodelines\Interfaces\FactoryInterface;
use Kodelines\Middleware\AuthMiddleware;

class Factory implements FactoryInterface
{

  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  public static function loadRoutes(RouteCollectorProxy $app): RouteCollectorProxy {


    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $app->post('/clients', \Elements\Clients\Controllers\ClientsController::class . ':create')->setName('clients.create');

      $app->put('/clients', \Elements\Clients\Controllers\ClientsController::class . ':update')->setName('clients.update');

      $app->delete('/clients', \Elements\Clients\Controllers\ClientsController::class . ':delete')->setName('clients.delete');

      $app->get('/clients', \Elements\Clients\Controllers\ClientsController::class . ':list')->setName('clients.list');

      $app->get('/clients/{id}', \Elements\Clients\Controllers\ClientsController::class . ':get')->setName('clients.get');

    })->add(new AuthMiddleware("superadministrator"));
    
    return $app;
  }
 
}

?>