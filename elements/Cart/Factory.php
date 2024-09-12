<?php

declare(strict_types=1);

namespace Elements\Cart;

use Slim\Routing\RouteCollectorProxy;
use Kodelines\Interfaces\FactoryInterface;

class Factory implements FactoryInterface
{

  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  public static function loadRoutes(RouteCollectorProxy $app): RouteCollectorProxy {

   
    /**
     * Carrello
     */
    $app->get('/cart', \Elements\Cart\Controllers\CartController::class . ':get')->setName('cart.get');

    $app->post('/cart', \Elements\Cart\Controllers\CartController::class . ':create')->setName('cart.create');

    $app->put('/cart', \Elements\Cart\Controllers\CartController::class . ':update')->setName('cart.update');

    $app->get('/cart/full', \Elements\Cart\Controllers\CartController::class . ':fullGet')->setName('cart.fullGet');

    $app->get('/cart/products', \Elements\Cart\Controllers\CartController::class . ':products')->setName('cart.products');

    $app->post('/cart/product', \Elements\Cart\Controllers\ProductsController::class . ':add')->setName('cart.product.add');

    $app->get('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':get')->setName('cart.products.get');

    $app->put('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':edit')->setName('cart.products.edit');

    $app->delete('/cart/product/{id}', \Elements\Cart\Controllers\ProductsController::class . ':delete')->setName('cart.products.delete');


    return $app;
  }
 
}

?>