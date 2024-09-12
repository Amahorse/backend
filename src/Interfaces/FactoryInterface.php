<?php

declare(strict_types=1);

namespace Kodelines\Interfaces;

use Slim\Routing\RouteCollectorProxy;

interface FactoryInterface
{


  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  public static function loadRoutes(RouteCollectorProxy $app): RouteCollectorProxy; 
 
}

?>