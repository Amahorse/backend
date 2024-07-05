<?php

declare(strict_types=1);

namespace Elements\Resellers;

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

      $api->post('/resellers', \Elements\Resellers\Controllers\ResellersController::class . ':create')->setName('resellers.create');

      $api->put('/resellers/{id}', \Elements\Resellers\Controllers\ResellersController::class . ':update')->setName('resellers.update');
  
      $api->delete('/resellers/{id}', \Elements\Resellers\Controllers\ResellersController::class . ':delete')->setName('resellers.delete');
      
    })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>