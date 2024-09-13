<?php

declare(strict_types=1);

namespace Elements\Store;

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

      $api->post('/store', \Elements\Store\Controllers\StoreController::class . ':create')->setName('store.create');
  
      $api->put('/store/{id}', \Elements\Store\Controllers\StoreController::class . ':update')->setName('store.edit');
      
      $api->delete('/store/{id}', \Elements\Store\Controllers\StoreController::class . ':delete')->setName('store.delete');

    })->add(new AuthMiddleware("administrator"));



      //B2b
      $app->get('/store/b2b', \Elements\Store\Controllers\B2bController::class . ':list')->setName('store.b2b.list');
    
      $app->get('/store/b2b/{id:[0-9]+}', \Elements\Store\Controllers\B2bController::class . ':get')->setName('store.b2b.get');

      $app->get('/store/b2b/{slug}', \Elements\Store\Controllers\B2bController::class . ':slug')->setName('store.b2b.slug');

      
      //Equestro
      $app->get('/store/equestro', \Elements\Store\Controllers\B2bController::class . ':list')->setName('store.equestro.list');
    
      $app->get('/store/equestro/{id:[0-9]+}', \Elements\Store\Controllers\B2bController::class . ':get')->setName('store.equestro.get');

      $app->get('/store/equestro/{slug}', \Elements\Store\Controllers\B2bController::class . ':slug')->setName('store.equestro.slug');

      //Acavallo
      $app->get('/store/acavallo', \Elements\Store\Controllers\AcavalloController::class . ':list')->setName('store.acavallo.list');

      $app->get('/store/acavallo/{id:[0-9]+}', \Elements\Store\Controllers\AcavalloController::class . ':get')->setName('store.acavallo.get');

      $app->get('/store/acavallo/{slug}', \Elements\Store\Controllers\AcavalloController::class . ':slug')->setName('store.acavallo.slug');
    

      //Generico
      $app->get('/store', \Elements\Store\Controllers\StoreController::class . ':list')->setName('store.list');
    
      $app->get('/store/{id:[0-9]+}', \Elements\Store\Controllers\StoreController::class . ':get')->setName('store.get');

      $app->get('/store/{slug}', \Elements\Store\Controllers\StoreController::class . ':slug')->setName('store.slug');

      $app->get('/store/{id:[0-9]+}/images', \Elements\Store\Controllers\StoreController::class . ':images')->setName('store.get.images');
    

    return $app;
  }
 
}

?>