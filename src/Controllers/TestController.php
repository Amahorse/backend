<?php


declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Db;
use Kodelines\Abstract\Controller;
use Elements\Products\Import as Products;
use Elements\Categories\Import as Categories;
use Elements\Brands\Import as Brands;
use Elements\Store\Import as Store;
use Elements\Users\Import as Users;
use Elements\Store\Store as St;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TestController extends Controller
{


    public function get(Request $request, Response $response, array $args) : Response
    {   


        return $this->response($response,St::list());
        Db::getInstance()->skipError = true;

        return $this->response($response,true);
        if($args['type'] == 'store') {
            Store::start();
        } elseif($args['type'] == 'products') {
            Products::start();
        } elseif($args['type'] == 'categories') {
            Categories::start();
        } elseif($args['type'] == 'brands') {
            Brands::start();
        } elseif($args['type'] == 'icons') {
            Products::icons();
        } elseif($args['type'] == 'prices') {
            Store::prices();
        } elseif($args['type'] == 'availabilities') {
            Store::availabilities();
        } elseif($args['type'] == 'users') {
            Users::start();
        } 
        else {
            return $this->response($response,false);
        }
        

        return $this->response($response,true);
    }


}