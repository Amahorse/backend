<?php
declare(strict_types=1);

namespace Elements\Orders\Controllers;

use Kodelines\Abstract\Controller;
use Elements\Orders\Stats;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class StatsController extends Controller {

  /**
   * Ritorna statistiche degli ordini
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function orders(Request $request, Response $response) : Response {

    return $this->response($response,Stats::orders($this->data));

  }

  /**
   * Ritorna statistiche dei prodotti
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function products(Request $request, Response $response,array $args) : Response {

    return $this->response($response,Stats::products($this->data));

  }


}
