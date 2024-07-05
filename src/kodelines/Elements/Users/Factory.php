<?php

declare(strict_types=1);

namespace Elements\Users;

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


    $app->put('/users/password/{id}/setup', \Elements\Users\Controllers\PasswordController::class . ':setup')->setName('password.setup');

    $app->put('/users/password/{id}/recover/{key}', \Elements\Users\Controllers\PasswordController::class . ':recover')->setName('password.recover');

    $app->post('/users/password/lost', \Elements\Users\Controllers\PasswordController::class . ':lost')->setName('password.lost');

    $app->get('/users/confirm/{id}/{key}', \Elements\Users\Controllers\UsersController::class . ':confirm')->setName('users.confirm');

    $app->post('/users/activation', \Elements\Users\Controllers\UsersController::class . ':sendActivation')->setName('users.activation');

    //Backend
    $app->group('', function (RouteCollectorProxy $api) use ($app) {

      $api->get('/users/{id}', \Elements\Users\Controllers\UsersController::class . ':get')->setName('users.get');

      $api->put('/users/{id}', \Elements\Users\Controllers\UsersController::class . ':update')->setName('users.update');

      $api->get('/users/{id_users}/data', \Elements\Data\Controllers\DataController::class . ':user')->setName('users.data.get');

      $api->get('/users', \Elements\Users\Controllers\UsersController::class . ':list')->setName('users.list');
      
      $api->post('/users', \Elements\Users\Controllers\UsersController::class . ':create')->setName('users.create');
  
      $api->delete('/users/{id}', \Elements\Users\Controllers\UsersController::class . ':delete')->setName('users.delete');
  
      $api->put('/users/password/{id}', \Elements\Users\Controllers\PasswordController::class . ':update')->setName('password.update');

    })->add(new AuthMiddleware("administrator"));

    // Dentro a al controller profile c'è un costruct che controlla utente corrente e blinda le azioni a quell'utente
    //Front end
    $app->group('/profile', function (RouteCollectorProxy $profile) use ($app) {

      $profile->get('', \Elements\Users\Controllers\ProfileController::class . ':get')->setName('profile.get');

      $profile->put('', \Elements\Users\Controllers\ProfileController::class . ':update')->setName('profile.update');
  
      $profile->put('/password', \Elements\Users\Controllers\ProfileController::class . ':password')->setName('profile.password.update');

      $profile->post('/password/setup', \Elements\Users\Controllers\ProfileController::class . ':setupPassword')->setName('profile.password.setup');

      $profile->get('/data', \Elements\Users\Controllers\ProfileController::class . ':data')->setName('profile.data.list');

      $profile->get('/data/{id}', \Elements\Users\Controllers\ProfileController::class . ':data')->setName('profile.data.get');

      $profile->put('/data/{id}', \Elements\Users\Controllers\ProfileController::class . ':data')->setName('profile.data.update');

      $profile->delete('/data/{id}', \Elements\Users\Controllers\ProfileController::class . ':data')->setName('profile.data.delete');

      $profile->get('/orders', \Elements\Users\Controllers\ProfileController::class . ':orders')->setName('profile.orders.list');

      $profile->get('/orders/{id}', \Elements\Users\Controllers\ProfileController::class . ':orders')->setName('profile.orders.get');


    })->add(new AuthMiddleware("not_confirmed"));

    return $app;
  }
 
}

?>