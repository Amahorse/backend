<?php


declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Db;
use Kodelines\Abstract\Controller;
use Elements\Products\Import as Products;
use Elements\Categories\Import as Categories;
use Elements\Brands\Import as Brands;
use Elements\Import\Store;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TestController extends Controller
{


    public function get(Request $request, Response $response, array $args) : Response
    {   

        Db::getInstance()->skipError = true;


        if($args['type'] == 'store') {
            Store::start();
        } elseif($args['type'] == 'products') {
            Products::start();
        } elseif($args['type'] == 'categories') {
            Categories::start();
        } elseif($args['type'] == 'brands') {
            Brands::start();
        } else {
            return $this->response($response,false);
        }
        

        return $this->response($response,true);
    }


}