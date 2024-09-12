<?php

declare(strict_types=1);

namespace Elements\Products;

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

    $app->get('/products', \Elements\Products\Controllers\ProductsController::class . ':list')->setName('products.list');

    $app->get('/products/{id}', \Elements\Products\Controllers\ProductsController::class . ':get')->setName('products.get');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {
 
      $api->post('/products', \Elements\Products\Controllers\ProductsController::class . ':create')->setName('products.create');
  
      $api->put('/products/{id}', \Elements\Products\Controllers\ProductsController::class . ':update')->setName('products.update');
  
      $api->delete('/products/{id}', \Elements\Products\Controllers\ProductsController::class . ':delete')->setName('products.delete');

  })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>