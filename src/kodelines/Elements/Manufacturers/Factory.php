<?php

declare(strict_types=1);

namespace Elements\Manufacturers;

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

    $app->get('/manufacturers', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':list')->setName('manufacturers.list');

    $app->get('/manufacturers/{id:[0-9]+}', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':get')->setName('manufacturers.get');

    $app->get('/manufacturers/{id:[0-9]+}/images', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':images')->setName('manufacturers.get.images');

    $app->get('/manufacturers/{slug}', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':slug')->setName('manufacturers.slug');

    $app->get('/manufacturers/{slug}/images', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':images')->setName('manufacturers.slug.images');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->post('/manufacturers', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':create')->setName('manufacturers.create');

      $api->put('/manufacturers/{id:[0-9]+}', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':update')->setName('manufacturers.update');
  
      $api->delete('/manufacturers/{id:[0-9]+}', \Elements\Manufacturers\Controllers\ManufacturersController::class . ':delete')->setName('manufacturers.delete'); 

    })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>