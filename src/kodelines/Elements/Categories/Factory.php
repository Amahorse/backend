<?php

declare(strict_types=1);

namespace Elements\Categories;

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

    $app->get('/categories', \Elements\Categories\Controllers\CategoriesController::class . ':list')->setName('categories.list');

    $app->get('/categories/main', \Elements\Categories\Controllers\CategoriesController::class . ':main')->setName('categories.list.main');

    $app->get('/categories/{id:[0-9]+}', \Elements\Categories\Controllers\CategoriesController::class . ':get')->setName('categories.get');

    $app->get('/categories/{slug}', \Elements\Categories\Controllers\CategoriesController::class . ':slug')->setName('categories.slug');

    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->post('/categories', \Elements\Categories\Controllers\CategoriesController::class . ':create')->setName('categories.create');

      $api->put('/categories/{id}', \Elements\Categories\Controllers\CategoriesController::class . ':update')->setName('categories.update');
  
      $api->delete('/categories/{id}', \Elements\Categories\Controllers\CategoriesController::class . ':delete')->setName('categories.delete');

    })->add(new AuthMiddleware("administrator"));
 

    return $app;
  }
 
}

?>