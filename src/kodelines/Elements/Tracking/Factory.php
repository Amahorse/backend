<?php

declare(strict_types=1);

namespace Elements\Tracking;

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

    $app->put('/tracking', \Elements\Tracking\Controllers\ActionsController::class . ':updateCurrent')->setName('tracking.actions.update');

    $app->post('/tracking/actions', \Elements\Tracking\Controllers\ActionsController::class . ':create')->setName('tracking.actions.create');

    return $app;
  }
 
}

?>