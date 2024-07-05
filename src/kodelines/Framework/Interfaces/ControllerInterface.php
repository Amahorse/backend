<?php

declare(strict_types=1);

namespace Kodelines\Interfaces;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface ControllerInterface
{

   public function list(Request $request, Response $response) : Response;

   public function get(Request $request, Response $response,array $args) : Response;

   public function create(Request $request, Response $response) : Response;

   public function update(Request $request, Response $response,array $args) : Response;

   public function delete(Request $request, Response $response,array $args) : Response;

   
 
}

?>