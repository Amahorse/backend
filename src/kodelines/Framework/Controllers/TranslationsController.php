<?php

declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\App;
use Kodelines\Helpers\Translations;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TranslationsController extends Controller
{

    public function get(Request $request, Response $response, array $args) : Response
    {   
        App::getInstance()->language = $args['language'];

        return $this->response($response,
            [
              'general' => Translations::getFile('general'),
              'messages' => Translations::getFile('messages'),
              'validator' => Translations::getFile('validator')
            ]
      
          );

    }


}