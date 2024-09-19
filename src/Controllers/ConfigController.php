<?php


declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\App;
use Kodelines\Abstract\Controller;
use Kodelines\Helpers\Locale;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ConfigController extends Controller
{


    public function get(Request $request, Response $response, array $args) : Response
    {   
        //TODO: questo non è x bu in teoria
        if($domainHeader = $request->getHeaderLine("X-Bu-Domain")) {
            App::getInstance()->domain = $domainHeader;
        }

        App::getInstance()->config->generate();

        $reserved = App::getInstance()->config->reserved;

        $config = App::getInstance()->config->values;

        foreach($reserved as $group => $value) {
            
            if(is_string($value) && isset($config[$value])) {

                unset($config[$value]);

                continue;
            }


            if(is_array($value)) {

                foreach($value as $subval) {
                    if(array_key_exists($group,$config) && isset($config[$group][$subval])) {
                        unset($config[$group][$subval]);
                    }
                }

            } 
                     
        }
   
        return $this->response($response,$config);
    }


}