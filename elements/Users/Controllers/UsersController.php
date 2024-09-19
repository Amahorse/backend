<?php
 /**
  * @author Giordano Pierini <giordanopierini@gmail.com>
  * @copyright 2018 Kodelines
  * @category Admin Controllers
  * @version 0.1b
  */

namespace Elements\Users\Controllers;

use Kodelines\Oauth\Scope;

use Kodelines\Key;
use Kodelines\Db;
use Kodelines\Mailer;
use Kodelines\Abstract\Controller;
use Kodelines\Exception\ValidatorException;
use Kodelines\Helpers\Password;
use Elements\Data\Data;
use Elements\Users\Users;
use Kodelines\Interfaces\ModelInterface;
use Elements\Agents\Agents;
use Elements\Resellers\Resellers;
use Kodelines\Tools\Validate;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


class UsersController extends Controller {

  //TODO: Creare tabella a parte con password, hash e storico password per farci join sono su controllo ed evitare campi password in risposta

    public ModelInterface $model;

    /**
     * Campi nascosti
     *
     * @var array
     */
    public $hidden = [
      "hash",
      "password",
      "password_repeat",
    ];

  /**
   * Funzione per fixare dati pre inserimento e modifica
   *
   * @return void
   */
  public function fix():void {
      $this->data = Users::fix($this->data);
  }

  /**
   * Get Utente
   *
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   */
  public function get(Request $request, Response $response,array $args) : Response{

    if(!$this->model->object = Users::get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    //TODO: questo se attivato non faceva aprire gli ordini sul backoffice, da trovare scappatoia
    //Controllo se utente corrente puà modificare quello con id selezionato
    if(!Users::canEdit($this->model->object)) {
      //throw new HttpUnauthorizedException($request);
    }

    return $this->response($response,$this->parse($this->model->object));


  }

  /**
   * Crea Utente
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function create(Request $request, Response $response) : Response
  {


    //Controllo password non viene fato dal validator del model perchè obbligatoria solo in fase di creazione

    if(empty($this->data['password'])) {
      throw new ValidatorException('password_different');
    }

    if(empty($this->data['password_repeat']) || $this->data['password_repeat'] <> $this->data['password']) {
      throw new ValidatorException('password_different');
    }

    //Fixo dati $this->data
    $this->fix();

    //create password and hash, throws validator exception if wron
    $pass = Password::create($this->data['password']);

    //Override password with encoded value and assign hash
    $this->data['password'] = $pass['password'];

    $this->data['hash'] = $pass['hash'];


    //Inserisco utente da model
    $user = $this->model->create($this->data);

    //Inserisco dati di spedizione, se non trovati o non validi li prende da utente 
    if(empty($this->data['shipping']) || !Data::validate($this->data['shipping'],true)) {
    
      if(!$this->data['shipping'] = Data::generateFromUser($user,$user['type'])) {

        $skip_shipping = true;
      }

    } 

    if(!isset($skip_shipping)) {
      Data::set($user['id'],'users',$this->data['shipping']);
    }



    //Creo profilo agent se autorizzazione è agente o agent
    if($user['auth'] == Scope::code("agent")) {

      Agents::createByUser($user['id']);

    }

    if($user['auth'] == Scope::code("reseller")) {

      Resellers::createByUser($user['id']);

    }  
 
    return $this->response($response,$this->parse($user));

  }


    /**
     * Registrazione utente
     *  TODO: spostato da Oauth Register
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
            Mailer::queue('account-confirm', $user['id'], $this->data, _APP_LANGUAGE_,1);
    
            
          //Business user with valid vat number
         } else if (!empty($args['business']) && $vat_number_verified == 1) {
    
             $key = Key::build($user['id'],'account-confirm',7);
    
             $this->data['link'] =  $full_url .'/api/users/confirm/'.$user['id'].'/'.$key;
    
             //send email to confirm account
             Mailer::queue('account-confirm', $user['id'], $this->data, _APP_LANGUAGE_,1);
    
     
          } else {
    
            //Business user with not valid vat number must be confirmed by administrators
    
            $key = Key::build($user['id'],'account-confirm',7);
            
            //TODO: accrocco da rimuovere nel nuovo front   
            $this->data['link'] =  $full_url .'/api/users/confirm/'.$user['id'].'/'.$key;
    
            //send email to confirm account
            Mailer::queue('account-confirm-business', config('app','administrator'), $this->data, _APP_LANGUAGE_,1);
   
          }
    
  
    
        return $this->response($response,Token::generate(_OAUTH_CLIENT_ID_,$user));

    }


  /**
   * Modifica utente
   *
   * @param Request $request
   * @param Response $response
   * @param array $args
   * @return Response
   */
  public function update(Request $request, Response $response, array $args) : Response
  {

    if(!$this->model->object = Users::get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

    //Controllo se utente corrente puà modificare quello con id selezionato
    if(!Users::canEdit($this->model->object)) {
      throw new HttpUnauthorizedException($request);
    }

    //Fixo dati $this->data
    $this->fix();

    //Inserisco utente da model
    $user = $this->model->update($args['id'],array_merge($this->model->object,$this->data));

    //Shipping Data
    if(!empty($this->data['shipping'])) {
  
      if(Data::validate($this->data['shipping'],true)) {

        if(empty($this->data['shipping']['id_data'])) {

          Data::set($user['id'],'users',$this->data['shipping']);

        } else {
  
          if(isset($this->data['shipping']['delete'])) {
            Data::delete($this->data['shipping']['id_data']);
          } else {
            Data::set($user['id'],'users',$this->data['shipping'],$this->data['shipping']['id_data']);
          }
  
    
        }

      }

    }  

    return $this->response($response,$this->parse($user));
  }


  /**
   * Elimina elemento da id
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function delete(Request $request, Response $response,array $args) : Response{



    if(empty($args['id'])) {
      throw new HttpNotFoundException($request);
    }

    if(!$this->model->object = Users::get(id($args['id']))) {
      throw new HttpNotFoundException($request);
    }

     //Controllo se utente corrente puà modificare quello con id selezionato
     if(!Users::canEdit($this->model->object)) {
      throw new HttpUnauthorizedException($request);
    }


    $this->model->delete(id($args['id']));

    return $this->response($response,true);


  }


  /**
   * Lista utenti
   *
   * @param Request $request
   * @param Response $response
   * @return Response
   */
  public function list(Request $request, Response $response) : Response
  {

    if(empty($this->data['auth_max'])) {
      $this->data['auth_max'] = user('auth');
    }

    if(empty($this->data['limit'])) {
      $this->data['limit'] = 100;
    }

    
    return $this->response($response,$this->model->list($this->data));

  }



  /**
   * Api per confermare l'utente da front end, reindirizza a pagina di messaggio
   *
   * @param Request $request
   * @param Response $response
   * @param [type] $args
   * @return Response
   */
  public function confirm(Request $request, Response $response,array $args) : Response{


    try {

      if(!isset($args['id']) || !isset($args['key']) || !Key::check($args['id'],$args['key'],'account-confirm')) {
        throw new ValidatorException('key_not_valid_or_expired');
      }

      if(!$user = Users::get(id($args['id']))) {
        throw new ValidatorException('user_not_found');
      }

      if(!Db::update('users','auth',Scope::code('user'),'id',id($args['id']))) {
        throw new ValidatorException('database_error');
      }


      Mailer::queue('account-created', id($args['id']), $user, false,2);

      redirect($this->container->get('cms')->permalink->message('account_created','success'));


    } catch (ValidatorException $e) {

      redirect($this->container->get('cms')->permalink->message($e->getMessage(),'error'));

    }


    return $this->response($response,true);


  }


      /**
     * Confirm user account validating a key
     * @api
     * @method GET
     * @return Boolean
     */

    public function sendActivation(Request $request, Response $response,array $args) : Response{

        //TODO: sistemare
        $full_url = 'https://domain.com';

   
        if(empty($this->data['email'])) {
          throw new ValidatorException('missing_field_email');
        }

        if(!Validate::isEmail($this->data['email'])) {
          throw new ValidatorException('wrong_email');
        }

        if(!$user = Users::where('email',$this->data['email'])) {
          throw new ValidatorException('user_not_found');
        }

        //Utente che ha già effettuato registrazione ma non ha confermato
        if($user['auth'] != Scope::code('not_confirmed') && $user['auth'] != Scope::code('provisional')) {
          throw new ValidatorException('user_already_confirmed');
        }

        $key = Key::build($user['id'],'account-confirm',7);


        $user['link'] = $full_url . '/api/users/confirm/'.$user['id'].'/'.$key;

        Mailer::queue('account-confirm', $user['id'], $user, false,1);


        return $this->response($response,true);



    }





}
