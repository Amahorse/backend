<?php

declare(strict_types=1);

namespace Elements\Agents;

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

      $api->post('/agents', \Elements\Agents\Controllers\AgentsController::class . ':create')->setName('agents.create');

      $api->put('/agents/{id}', \Elements\Agents\Controllers\AgentsController::class . ':update')->setName('agents.update');
  
      $api->delete('/agents/{id}', \Elements\Agents\Controllers\AgentsController::class . ':delete')->setName('agents.delete');

  })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>