<?php


declare(strict_types=1);

namespace Kodelines\Controllers;

use Kodelines\Abstract\Controller;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Tools\Json;
use Kodelines\Tools\Folder;

class DocsController extends Controller
{

    public function get(Request $request, Response $response, array $args) : Response
    {   

        if(!empty($args['element'])) {
                
            $json = Json::arrayFromFile(_DIR_ELEMENTS_ . $args['element'] . '/docs.json');
    
            return $this->response($response,$json);
        }

        $json = Json::arrayFromFile(_DIR_ROOT_ . 'docs.json');

        foreach(Folder::read(_DIR_ELEMENTS_) as $element) {

            if(file_exists(_DIR_ELEMENTS_ . $element . '/docs.json')) {

                $elementJson =  Json::arrayFromFile(_DIR_ELEMENTS_ . $element . '/docs.json');
              
                if(isset($elementJson['paths'])) {
                    $json['paths'] = array_merge($json['paths'],$elementJson['paths']);
                }

                if(isset($elementJson['definitions'])) {
                    $json['definitions'] = array_merge($json['definitions'],$elementJson['definitions']);
                }

                if(isset($elementJson['parameters'])) {
                    $json['parameters'] = array_merge($json['parameters'],$elementJson['parameters']);
                }

            }
        }

        return $this->response($response,$json);
    }


}