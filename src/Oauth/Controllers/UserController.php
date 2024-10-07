<?php


declare(strict_types=1);

namespace Kodelines\Oauth\Controllers;

use Kodelines\Context;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Oauth\Token;
use Kodelines\Oauth\User;
use Kodelines\Abstract\Controller;

class UserController extends Controller
{

  
    /**
     * Oauth Login controller
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function login(Request $request, Response $response, array $args) : Response
    {   
     
        if(empty($this->data["client_id"])) {
          throw new HttpBadRequestException($request,'client_id_required');
        }

        //Prendo da container il parametro di configurazione per l'identificatore
        $identifier = Context::$parameters['token']['identifier'];

        //Controllo se identificatore utente (email o userame è presente) o password è presente
        if(empty($this->data[Context::$parameters['token']['identifier']])) {
          throw new HttpBadRequestException($request,'missing_username');
        }

        if(empty($this->data['password'])) {
          throw new HttpBadRequestException($request,'missing_password');
        }

        if(!$user = User::checkCredentials($this->data[$identifier],$identifier,$this->data['password'])) {
          throw new HttpUnauthorizedException($request,'wrong_credentials');
        }

        $token = new Token($request);

        $token->user = $user;

        $token->generate($this->data["client_id"]);

        return $this->response($response,$token->createResponse());

    }

       /**
     * Oauth Login controller
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function logout(Request $request, Response $response, array $args) : Response
    {   

      if(!defined('_OAUTH_TOKEN_') && !defined('_OAUTH_CLIENT_ID_')) {
        throw new HttpBadRequestException($request,'token_required');
      }

      if(!Token::revoke(_OAUTH_TOKEN_, _OAUTH_CLIENT_ID_)) {
        throw new HttpUnauthorizedException($request,'wrong_credentials');
      }

      Context::$token = new Token($request,['client_id' => _OAUTH_CLIENT_ID_]);

      return $this->response($response,Context::$token->createResponse());

    }



}