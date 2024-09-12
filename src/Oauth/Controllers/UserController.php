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
use Kodelines\Key;
use Kodelines\Mailer;
use Kodelines\Tools\Validate;
use Kodelines\Helpers\Password;
use Kodelines\Tools\Str;
use Elements\Users\Users;
use Elements\Users\Models\UsersModel;
use Elements\Tracking\Tracking;
use Elements\Newsletter\Subscriptions;


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
        if(empty($this->data[config('token','identifier')]) || empty($this->data['password'])) {
          throw new HttpBadRequestException($request,'missing_username_or_password');
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
     * Registrazione utente
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function register(Request $request, Response $response, array $args) : Response
    {

        $users = new UsersModel;

        //TODO: controllo anche tipo utente valido
        if(empty($this->data['type'])) {
          $this->data['type'] = config('store','type');
        }

        if(!isset($this->data["privacy"])) {
            throw new HttpBadRequestException($request,'privacy_not_set');
        }

        //Check autorizzazioni email, se esiste utente che può registrarsi setta id su this->data
        $this->checkAuth($request);
  
        //Setto autorizzazioni di base con quelle default, per business possono essere sovrascritte
        $this->data['auth'] = Scope::code(config('default','scope'));

        if($this->data['type'] !== 'b2c') {
            
    
            if(config('users','b2b_auto_confirm') == true) {
    
              //Conferma utente direttamente
              $vat_number_verified = 1;

              $this->data['auth'] = Scope::code('user');
                
              //TODO: questo non è necessariamente "IT"
            } elseif(config('users','b2b_vies_verify') == false || !$vies = Validate::viesCheckVAT("IT",$this->data['vat_number'])) {
    
              //Non conferma mai
              $vat_number_verified = 0;
    
            } else {
    
              //Conferma dipende da verifica tramite vies
              if(!isset($vies) || $vies['valid'] !== 'true') {
    
                $vat_number_verified = 0;
    
              } else {
    
                $this->data['business_name'] = $vies['name'];
    
                $vat_number_verified = 1;

                $this->data['auth'] = Scope::code('user');
    
              }
    
            }
    
    
        }
        
        $this->data = $users->fix($this->data);

        //TODO: accrocco fatto per massimo, questo non dovrebbe funzionare cosi è l'inverso, dovrebbe esserci un campo per le auth in base al form
        if(!isset($this->data['create_account']) || (isset($this->data['create_account']) && !empty($this->data['create_account']))) {
          
          if(empty($this->data['password'])) {
            throw new HttpBadRequestException($request,'missing_password');
          }
    
          if(empty($this->data['password_repeat']) || ($this->data['password_repeat'] <> $this->data['password'])) {
              throw new HttpBadRequestException($request,'password_different');
          }

          if(empty($this->data[config('token','identifier')])) {
              throw new HttpBadRequestException($request,config('token','identifier') . ' ' . 'is_required');
          }

          $this->data['auth'] = Scope::code('not_confirmed');

        } else {

          $this->data['password'] = Str::random(10);
          
        }
        

  
        //create password and hash
        $pass = Password::create($this->data['password']);

        //Override password with encoded value and assign hash
        $this->data['password'] = $pass['password'];

        $this->data['hash'] = $pass['hash'];

        $this->data['id_tracking'] = Tracking::getCurrentId();


        //Associate user if tracking is from agent or resellers
        if($tracking = Tracking::getCurrent()) {

            if(!empty($tracking['id_agents'])) {
                $this->data['id_agents'] = $tracking['id_agents']; 
            }
  
            if(!empty($tracking['id_resellers'])) {
                $this->data['id_resellers'] = $tracking['id_resellers']; 
            }
  
        }
  


        //DA QUI INSERISCE O AGGIORNA ROBA NEL DATABASE 
        if(isset($this->data['id'])) {
          
          //In caso di utente in stato checkout o eliminato resetto le autorizzazioni e lo metto in stato "non confermato"
          $user = $users->update(id($this->data['id']),$this->data);

          //NOTE: questo è per sicurezza ma potrebbe essere non necessario se il model lo recupera
          $user['id'] = $this->data['id'];

        } else {
          $user = $users->create($this->data);
        }

        //Se settato newsletter lo iscrivo
        if(isset($this->data['newsletter'])) {

            Subscriptions::subscribe($this->data['email']);
    
        }



        //QUI FACCIO I CONTROLLI SUI VARI TIPI DI EMAIL CHE DEVO MANDARE IN BASE A TIPO DI UTENTE CONFERMATO
        //TODO: sistemare con request o impostazione
        //Fix link referer
        // 1. write the http protocol
        $full_url = "http://";

        // 2. check if your server use HTTPS
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on") {
            $full_url = "https://";
        }

        // 3. append domain name
        $full_url = 'https://domain.com';

        if(empty($args['business'])) {

            $key = Key::build($user['id'],'account-confirm',7);
    
            $this->data['link'] =  $full_url .'/api/users/confirm/'.$user['id'].'/'.$key;
    
            //send email to confirm account
            Mailer::queue('account-confirm', $user['id'], $this->data, language(),1);
    
            
          //Business user with valid vat number
         } else if (!empty($args['business']) && $vat_number_verified == 1) {
    
             $key = Key::build($user['id'],'account-confirm',7);
    
             $this->data['link'] =  $full_url .'/api/users/confirm/'.$user['id'].'/'.$key;
    
             //send email to confirm account
             Mailer::queue('account-confirm', $user['id'], $this->data, language(),1);
    
     
          } else {
    
            //Business user with not valid vat number must be confirmed by administrators
    
            $key = Key::build($user['id'],'account-confirm',7);
            
            //TODO: accrocco da rimuovere nel nuovo front   
            $this->data['link'] =  $full_url .'/api/users/confirm/'.$user['id'].'/'.$key;
    
            //send email to confirm account
            Mailer::queue('account-confirm-business', config('app','administrator'), $this->data, language(),1);
   
          }
    
  
    
        return $this->response($response,Token::generate(_OAUTH_CLIENT_ID_,$user));

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