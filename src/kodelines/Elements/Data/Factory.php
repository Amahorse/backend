<?php

declare(strict_types=1);

namespace Elements\Data;

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
    
      $app->get('/data/{id}', \Elements\Data\Controllers\DataController::class . ':get')->setName('data.get');

      $app->put('/data/{id}', \Elements\Data\Controllers\DataController::class . ':update')->setName('data.update');

      $app->delete('/data/{id}', \Elements\Data\Controllers\DataController::class . ':delete')->setName('data.delete');

    return $app;
  }
 
}

?>