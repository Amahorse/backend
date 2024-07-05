<?php

declare(strict_types=1);

namespace Elements\Stores;

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

    $app->get('/stores', \Elements\Stores\Controllers\StoresController::class . ':list')->setName('stores.list');

    $app->get('/stores/{id}', \Elements\Stores\Controllers\StoresController::class . ':get')->setName('stores.get');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->post('/stores', \Elements\Stores\Controllers\StoresController::class . ':create')->setName('stores.create');

      $api->put('/stores/{id}', \Elements\Stores\Controllers\StoresController::class . ':update')->setName('stores.update');

      $api->delete('/stores/{id}', \Elements\Stores\Controllers\StoresController::class . ':delete')->setName('stores.delete');

    })->add(new AuthMiddleware("administrator"));
    
    return $app;
  }
 
}

?>