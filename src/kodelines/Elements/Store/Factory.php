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

      //PACCHETTI
      $api->post('/store/packages', \Elements\Store\Controllers\PackagesController::class . ':create')->setName('store.packages.create');
  
      $api->put('/store/packages/{id}', \Elements\Store\Controllers\PackagesController::class . ':update')->setName('store.packages.edit');
      
      //Identico a warehouse ma messo per uniformità
      $api->delete('/store/packages/{id}', \Elements\Store\Controllers\WarehouseController::class . ':delete')->setName('store.packages.delete');

      //MAGAZZINO
      $api->post('/store/warehouse', \Elements\Store\Controllers\WarehouseController::class . ':create')->setName('store.warehouse.create');
  
      $api->put('/store/warehouse/{id}', \Elements\Store\Controllers\WarehouseController::class . ':update')->setName('store.warehouse.edit');
  
      $api->delete('/store/warehouse/{id}', \Elements\Store\Controllers\WarehouseController::class . ':delete')->setName('store.warehouse.delete');
      
      //SCONTI
      $api->post('/store/discounts', \Elements\Store\Controllers\DiscountsController::class . ':create')->setName('store.discounts.create');
  
      $api->put('/store/discounts/{id}', \Elements\Store\Controllers\DiscountsController::class . ':update')->setName('store.discounts.edit');
  
      $api->delete('/store/discounts/{id}', \Elements\Store\Controllers\DiscountsController::class . ':delete')->setName('store.discounts.delete');
      
      //TASSE
      $api->post('/store/tax', \Elements\Store\Controllers\TaxController::class . ':create')->setName('store.tax.create');
  
      $api->put('/store/tax/{id}', \Elements\Store\Controllers\TaxController::class . ':update')->setName('store.tax.edit');
  
      $api->delete('/store/tax/{id}', \Elements\Store\Controllers\TaxController::class . ':delete')->setName('store.tax.delete');



    
    })->add(new AuthMiddleware("administrator"));

      //PACCHETTI
      $app->get('/store/packages', \Elements\Store\Controllers\PackagesController::class . ':list')->setName('store.packages.list');

      $app->get('/store/packages/{id:[0-9]+}', \Elements\Store\Controllers\PackagesController::class . ':get')->setName('store.packages.get');
    
      $app->get('/store/packages/{slug}', \Elements\Store\Controllers\PackagesController::class . ':get')->setName('store.packages.slug');
    
      //PRODOTTI IN VENDITA
      $app->get('/store', \Elements\Store\Controllers\StoreController::class . ':list')->setName('store.list');
    
      $app->get('/store/{id:[0-9]+}', \Elements\Store\Controllers\StoreController::class . ':get')->setName('store.get');

      $app->get('/store/{id:[0-9]+}/images', \Elements\Store\Controllers\StoreController::class . ':images')->setName('store.get.images');
    
      $app->get('/store/{slug}', \Elements\Store\Controllers\StoreController::class . ':slug')->setName('store.slug');
    

    return $app;
  }
 
}

?>