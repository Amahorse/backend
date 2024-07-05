<?php


declare(strict_types=1);

namespace Kodelines\Oauth\Controllers;

use Kodelines\Helpers\Cookie;
use Kodelines\Oauth\Token;
use Kodelines\Abstract\Controller;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class TokenController extends Controller
{

    /**
     * Get access token
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function authorize(Request $request, Response $response) : Response
    {        

        if(empty($this->data['client_id'])) {
          throw new HttpBadRequestException($request,'client_id_required');
        }
 
        $token = Token::generate($this->data['client_id']);

        //Per il code flow state è necessario ripassarlo per verifica
        if(!empty($this->data["state"])) {
          $token["state"] = $this->data["state"];
        }


        return $this->response($response,$token);
    }


     /**
     * Get access token
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function refresh(Request $request, Response $response) : Response
    {   

        if(empty($this->data['client_id'])) {
          throw new HttpBadRequestException($request,'client_id_required');
        }

        if(empty($this->data['refresh_token'])) {
          throw new HttpBadRequestException($request,'refresh_token_required');
        }  

        $token = Token::refresh($this->data["client_id"],$this->data['refresh_token']);
   
        //Per il code flow state è necessario ripassarlo per verifica
        if(!empty($this->data["state"])) {
          $token["state"] = $this->data["state"];
        }

 
     
        return $this->response($response,$token);
    }

    /**
     * TODO: accrocco per front and per recuperare cookie settato da backend
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function localStorageConvert(Request $request, Response $response) : Response
    {        

        if(!$cookie = Cookie::getInstance()->get('token')) {
          return $this->response($response,true);
        }

        if(!$cookie = json_decode(base64_decode($cookie),true)) {
          return $this->response($response,true);
        }

        Cookie::getInstance()->delete('token');   

        return $this->response($response,$cookie);
    }

}