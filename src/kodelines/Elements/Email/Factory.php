<?php

declare(strict_types=1);

namespace Elements\Email;

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
       
      $api->post('/email/accounts', \Elements\Email\Controllers\AccountsController::class . ':create')->setName('email.account.new');

      $api->put('/email/accounts/{id}', \Elements\Email\Controllers\AccountsController::class . ':update')->setName('email.account.new');

      $api->put('/email/templates/{id}', \Elements\Email\Controllers\TemplatesController::class . ':update')->setName('email.templates.update');

    })->add(new AuthMiddleware("administrator"));

    return $app;
  }
 
}

?>