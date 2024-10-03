<?php


declare(strict_types=1);

namespace Kodelines\Oauth\Controllers;

use Kodelines\Context;
use Kodelines\Oauth\Token;
use Kodelines\Abstract\Controller;
use Slim\Exception\HttpBadRequestException;
use Slim\Psr7\Request;
use Slim\Psr7\Response;


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
 
        Context::$token = new Token($request,['client_id' => $this->data['client_id']]);

        return $this->response($response,Context::$token->createResponse());
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

        //$token = Token::refresh($this->data["client_id"],$this->data['refresh_token']);

        $token = [];
     
        return $this->response($response,$token);
    }


}