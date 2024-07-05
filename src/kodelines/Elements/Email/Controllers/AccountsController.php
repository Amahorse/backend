<?php

declare(strict_types=1);

namespace Elements\Email\Controllers;


use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Email\Accounts;

class AccountsController extends Controller {

    public function create(Request $request, Response $response) : Response
    {   

        $this->data['status'] = 0;

        if(Accounts::test($this->data)) {
            $this->data['status'] = 1;
        } 
 
        return parent::create($request,$response);
    }

    public function update(Request $request, Response $response, $args) : Response
    {   

        $this->data['status'] = 0;

        if(Accounts::test($this->data)) {
            $this->data['status'] = 1;
        } 
 
        return parent::update($request,$response,$args);
    }

}

?>