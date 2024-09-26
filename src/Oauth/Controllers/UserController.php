<?php


declare(strict_types=1);

namespace Kodelines\Oauth\Controllers;


use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpBadRequestException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Kodelines\Oauth\Token;
use Kodelines\Oauth\User;
use Kodelines\Oauth\Scope;
use Kodelines\Abstract\Controller;
use Elements\Users\Users;


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

        //Controllo se identificatore utente (email o userame è presente) o password è presente
        if(empty($this->data[config('token','identifier')])) {
          throw new HttpBadRequestException($request,'missing_username');
        }

        if(empty($this->data['password'])) {
          throw new HttpBadRequestException($request,'missing_password');
        }

        if(!$user = User::checkCredentials($this->data[config('token','identifier')],config('token','identifier'),$this->data['password'])) {
          throw new HttpUnauthorizedException($request,'wrong_credentials');
        }

        $token = Token::generate($this->data["client_id"],$user, isset($this->data['remember']));

        return $this->response($response,$token);

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

        if(!Token::revoke(_OAUTH_TOKEN_, _OAUTH_CLIENT_ID_)) {
          throw new HttpUnauthorizedException($request,'wrong_credentials');
        }

        return $this->response($response,Token::generate(_OAUTH_CLIENT_ID_, []));

    }



    /**
     * Fa check autorizzazione email
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function check(Request $request, Response $response) : Response
    {   
      return $this->response($response,$this->checkAuth($request));
    }

    /**
     * Fa check autorizzazione email
     *
     * @param Request $request
     * @return Bool
     */
    private function checkAuth(Request $request):array|bool {

        //Questi sono controlli diversi da backend perchè deve restituire messaggi di errori più precisi e rimandare a pagine diverse
        if($check = Users::where('email',$this->data['email'])) {

          //Utente che ha già effettuato registrazione ma non ha confermato
          if($check['auth'] == Scope::code('not_confirmed')) {
            throw new HttpBadRequestException($request,'user_not_confirmed');
          }
  
          //Utente che si è già registrato ma non ha confermato
          if($check['auth'] == Scope::code('banned')) {
            throw new HttpBadRequestException($request,'user_banned');
          }
  
          //Utente che ha iniziato checkoiyt ma non ha finito l'ordine o utente eliminato è come se non esistesse, lo aggiorno
          if($check['auth'] == Scope::code('provisional') || $check['auth'] == Scope::code('inactive')) {

            $this->data['id'] = $check['id'];

          } else {
            //Altro caso all'infuori di questo è utente già esistente
            throw new HttpBadRequestException($request,'user_exists');

          }
  
  
      }

      return $check;

    }


}