<?php

declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Helpers\Currencies;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CurrenciesController extends Controller
{

    public function sync(Request $request, Response $response) : Response
    {   
      return $this->response($response,Currencies::sync());
    }

}