<?php

declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Settings;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class SettingsController extends Controller {


  public function update(Request $request, Response $response, $args) : Response {

    Settings::update($this->data);

    return $this->response($response,true);

  }


}

?>