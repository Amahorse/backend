<?php

use Slim\App as Slim;
use Slim\Routing\RouteCollectorProxy;
use Slim\Exception\HttpNotFoundException;
use Kodelines\Middleware\AuthMiddleware;
use Kodelines\Tools\Folder;

  /**
   * Chiamate standard per tutte le api
   *
   * @param RouteCollectorProxy $api
   * @return RouteCollectorProxy
   */
  function loadElementsRoutes(RouteCollectorProxy $api): RouteCollectorProxy {

    $loaded = [];


    //Carico route factory degli elementi base
    $baseDir = _DIR_ELEMENTS_;

    foreach(Folder::read($baseDir) as $element) {

      $factory = '\Elements\\' . ucfirst($element) . '\Factory';

      $loader = '\Elements\\' . ucfirst($element) . '\Factory::loadRoutes';

      //Carico elemento solo se 
      if(method_exists($factory,'loadRoutes') && !in_array($element,$loaded)) {
        
        $loaded[] = $element;

        $api = $loader($api);

      }

    }


    return $api;

  }
    

return function (Slim $app) {

    //Empty call to all routes
    $app->any('/', \Kodelines\Controllers\ApiController::class . ':empty')->setName('api.empty');

    //Options call to all routes
    $app->options('[/{params:.*}]', \Kodelines\Controllers\ApiController::class . ':preflight')->setName('api.preflight');

    //Configurazioni
    $app->get('/config', \Kodelines\Controllers\ConfigController::class . ':get')->setName('config.get');
 
    //Configurazioni
    $app->get('/docs', \Kodelines\Controllers\DocsController::class . ':get')->setName('docs.get');

    $app->get('/docs/{element}', \Kodelines\Controllers\DocsController::class . ':get')->setName('docs.element.get');

    //DATI GEOGRAFICI
    $app->get('/countries', \Kodelines\Controllers\CountriesController::class . ':list')->setName('countries.list');

    $app->get('/countries/{id}', \Kodelines\Controllers\CountriesController::class . ':get')->setName('countries.get');

    $app->get('/countries/{shortname}/shortname', \Kodelines\Controllers\CountriesController::class . ':getByShortName')->setName('countries.get.shortname');

    $app->get('/countries/{id}/states', \Kodelines\Controllers\CountriesController::class . ':states')->setName('countries.states');

    $app->get('/countries/states/{shortname}/shortname', \Kodelines\Controllers\CountriesController::class . ':getStateByShortName')->setName('countries.get.state.shortname');

    $app->get('/countries/{id}/regions', \Kodelines\Controllers\CountriesController::class . ':regions')->setName('countries.regions');

    //Autorizzazione
    $app->get('/oauth/token', \Kodelines\Oauth\Controllers\TokenController::class . ':authorize')->setName('oauth.token');

    $app->post('/oauth/token', \Kodelines\Oauth\Controllers\TokenController::class . ':refresh')->setName('oauth.refresh');

    $app->post('/oauth/login', \Kodelines\Oauth\Controllers\UserController::class . ':login')->setName('oauth.login');

    $app->post('/oauth/logout', \Kodelines\Oauth\Controllers\UserController::class . ':logout')->setName('oauth.logout');

    $app->post('/oauth/check', \Kodelines\Oauth\Controllers\UserController::class . ':check')->setName('oauth.check');

    //Test
    $app->get('/test/{type}', \Kodelines\Controllers\TestController::class . ':get')->setName('test.get');


    $app = loadElementsRoutes($app);

    $app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
      throw new HttpNotFoundException($request);
  });

};

?>