<?php

declare(strict_types=1);

namespace Elements\Orders;

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

    
    $app->put('/orders/{id}', Controllers\OrdersController::class . ':update')->setName('orders.update');

    $app->post('/orders/push', Controllers\OrdersController::class . ':push')->setName('orders.push');

    $app->post('/orders/{id}/confirm', Controllers\OrdersController::class . ':confirm')->setName('orders.confirm');

    $app->post('/orders/{id}/delete', Controllers\OrdersController::class . ':delete')->setName('orders.delete');

    $app->group('', function (RouteCollectorProxy $api) use ($app) { 

      $api->get('/orders', Controllers\OrdersController::class . ':get')->setName('orders.get');
  
      $api->get('/orders/full', Controllers\OrdersController::class . ':fullList')->setName('orders.full');
  
      $api->get('/orders/products/{id}', Controllers\ProductsController::class . ':get')->setName('orders.products.get');
  
      $api->put('/orders/products/{id}', Controllers\ProductsController::class . ':update')->setName('orders.products.update');
  
      $api->put('/orders/products/{id:[0-9]+}/preview', Controllers\ProductsController::class . ':preview')->setName('orders.products.update.preview');

      $api->put('/orders/products/{id:[0-9]+}/lotto', Controllers\ProductsController::class . ':lotto')->setName('orders.products.update.lotto');
  
      $api->get('/orders/products', Controllers\ProductsController::class . ':list')->setName('orders.products.list');
  
      $api->get('/orders/products/{id}/full', Controllers\ProductsController::class . ':fullGet')->setName('orders.products.fullGet');
  
      $api->get('/orders/export/{type}', Controllers\OrdersController::class . ':export')->setName('orders.export');
  
      $api->post('/orders/invoice/{id}/{type}', Controllers\OrdersController::class . ':invoice')->setName('orders.invoice'); 

    })->add(new AuthMiddleware());

    return $app;
  }
 
}

?>