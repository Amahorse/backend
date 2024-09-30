<?php

declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Config;
use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ConfigController extends Controller
{


    public function get(Request $request, Response $response, array $args) : Response
    {   
      
        $instance = new Config(defined('_APP_DOMAIN_') ? _APP_DOMAIN_ : false);

        $instance->generate();

        $reserved = $instance->reserved;

        $config = $instance->values;

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