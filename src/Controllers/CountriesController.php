<?php

declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\App;
use Kodelines\Helpers\Countries;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class CountriesController extends Controller
{

    public function list(Request $request, Response $response) : Response
    {   

        //Per applicazione blindata a una sola nazione faccio vedere in lista select solo quella
        if(!empty(App::getInstance()->client['id_countries'])) {
            return $this->response($response,[Countries::get(App::getInstance()->client['id_countries'])]);
        }

        return $this->response($response,Countries::list());
    }

    public function get(Request $request, Response $response, array $args) : Response
    {   
        return $this->response($response,Countries::get(id($args['id'])));
    }

    public function getByShortName(Request $request, Response $response, array $args) : Response
    {   
        return $this->response($response,Countries::getByShortName($args['shortname']));
    }

    public function getStateByShortName(Request $request, Response $response, array $args) : Response
    {   
        return $this->response($response,Countries::getStateByShortName($args['shortname']));
    }

    public function states(Request $request, Response $response, array $args) : Response
    {   
        return $this->response($response,Countries::listStates(id($args['id'])));
    }

    public function regions(Request $request, Response $response, array $args) : Response
    {    
        return $this->response($response,Countries::listRegions(id($args['id'])));
    }




}