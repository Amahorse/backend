<?php

declare(strict_types=1);

namespace Elements\Brands;

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

    $app->get('/brands', \Elements\Brands\Controllers\BrandsController::class . ':list')->setName('brands.list');

    $app->get('/brands/{id:[0-9]+}', \Elements\Brands\Controllers\BrandsController::class . ':get')->setName('brands.get');

    $app->get('/brands/{slug}', \Elements\Brands\Controllers\BrandsController::class . ':slug')->setName('brands.slug');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->post('/brands', \Elements\Brands\Controllers\BrandsController::class . ':create')->setName('brands.create');

      $api->put('/brands/{id}', \Elements\Brands\Controllers\BrandsController::class . ':update')->setName('brands.update');
  
      $api->delete('/brands/{id}', \Elements\Brands\Controllers\BrandsController::class . ':delete')->setName('brands.delete');

    })->add(new AuthMiddleware("administrator"));
 

    return $app;
  }
 
}

?>