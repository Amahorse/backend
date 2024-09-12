<?php


declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ApiController extends Controller
{

    public function preflight(Request $request, Response $response, array $args) : Response
    {   
        return $this->response($response,true);
    }


}