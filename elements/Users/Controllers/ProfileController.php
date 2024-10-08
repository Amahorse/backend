<?php
declare(strict_types=1);

namespace Elements\Users\Controllers;

use Kodelines\Abstract\Controller;
use Kodelines\Exception\ValidatorException;
use Kodelines\Exception\RuntimeException;
use Kodelines\Helpers\Password;
use Kodelines\Oauth\User;
use Kodelines\Oauth\Scope;
use Kodelines\Oauth\Token;
use Kodelines\Context;
use Kodelines\Db;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use DI\Container;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Elements\Data\Data;
use Elements\Users\Users;
use Elements\Orders\Orders;
use Elements\Orders\Products;
use Elements\Shipping\Shipping;

class ProfileController extends Controller {

    /**
     * Campi nascosti
     *
     * @var array
     */
    public $hidden = [
        "hash",
        "password",
        "password_repeat",
        "date_ins",
        "date_update",
        "discount_client_percentage",
        "discount_contract_percentage",
        "discount_final_percentage",
        "id",
        "id_gamma",
        "id_stores",
        "language",
        "role",
        "scope",
        "website",
        "website_ecommerce",
        "website_type"
      ];
  

    //Utente corrente;
    private $user = [];

    /**
     * In questa classe vengono recuperati i dati del profilo, se non è utente loggato torna errore
     * 
     * @param Container $container
     */
    public function __construct(Container $container) {

        parent::__construct($container);

        if(!user()) {
            throw new HttpForbiddenException($container->get('request'),'access_denied');
        }

        if(!$this->user = Users::get(user('id'))) {
            throw new HttpForbiddenException($container->get('request'),'access_denied');
        }
        
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

        return $this->response($response,$this->parse($this->user));

    }
    
    /*
    public function update(Request $request, Response $response, array $args) : Response
    {

        //Inserisco utente da model
        $user = Users::update($this->user['id'],Users::fix(array_merge($this->user,$this->data)));

        return $this->response($response,$this->parse($user));
    }


    public function password(Request $request, Response $response) : Response
    {

        if(empty($this->data['password'])) {
            throw new ValidatorException('missing_password');
        }

        if(!isset($this->data['old_password'])) {
            throw new ValidatorException('email_or_password_not_valid');
        }

        //Prendo da container il parametro di configurazione per l'identificatore
        $identifier = Context::$parameters['token']['identifier'];

        if (!User::checkCredentials($this->user[$identifier], $identifier, $this->data['old_password'])) {
            throw new ValidatorException('email_or_password_not_valid');    
        }
    
        if(!isset($this->data['password_repeat'])) {
            throw new ValidatorException('missing_password_repeat');
        }

        if($this->data['password_repeat'] <> $this->data['password']) {
            throw new ValidatorException('password_different');
        }

        Password::create($this->data['password'],$this->user['id']);

        //TODO: verificare queste email e fare spunta "Inviare mail a utente"
        //Mailer::queue('password-changed', $user['email'], $this->data,  $user['language'],3);

        return $this->response($response,true);
    }


    public function setupPassword(Request $request, Response $response) : Response
    {

        if(!isset($this->data['password']) || empty($this->data['password'])) {
          throw new ValidatorException('missing_password');
        }

        if(!isset($this->data['password_repeat']) || empty($this->data['password_repeat'])) {
          throw new ValidatorException('missing_password_repeat');
        }

        if($this->data['password_repeat'] <> $this->data['password']) {
          throw new ValidatorException('password_different');
        }

        if(!$pass = Password::create($this->data['password'])) {
          throw new ValidatorException('password_error');
        }

        $this->user['scope'] = Scope::code('user');

        //Update database
        Db::query("UPDATE users SET auth = ".$this->user['scope'].", password = ".encode($pass['password']).", hash = ".encode($pass['hash'])." WHERE id = " . id($this->user['id']));


        return $this->response($response,Token::generate(_OAUTH_CLIENT_ID_,$this->user));

      }



    public function data(Request $request, Response $response,array $args) : Response {

        //Modalità modifica/elimina
        if(!empty($args['id'])) {

            //Controllo esistenza dati
            if(!$data = Data::get($args['id'])) {
                throw new HttpNotFoundException($request);
            }

            //Controllo dati
            if($data['table_name'] !== 'users' || $data['table_id'] !== $this->user['id']) {
                throw new HttpNotFoundException($request);
            }

            if(mb_strtolower($request->getMethod()) == 'delete') {
                return $this->response($response,Data::delete($args['id']));
            }

            if(mb_strtolower($request->getMethod()) == 'put') {
                return $this->response($response,Data::update($args['id'],$this->data));
            }

            //Di default torna get
            return $this->response($response,$data);
        }

        $filters = ['table_name' => 'users', 'table_id' => $this->user['id']];

        //Per applicazione blindata a una sola nazione faccio vedere dati sono di quella
        if(!empty(Context::$store['id_countries'])) {
            $filters['id_countries'] = Context::$client['id_countries'];
        }

        //Modalità lista
        if(!$data = Data::list($filters)) {
            $data = [];
        }

    

        return $this->response($response,$data);

    }


    public function orders(Request $request, Response $response,array $args) : Response {

        //Modalità modifica/elimina
        if(!empty($args['id'])) {

            //Controllo esistenza dati
            if(!$order = Orders::fullGet($args['id'])) {
                throw new HttpNotFoundException($request);
            }
    
            //Controllo dati
            if($order['id_users'] != $this->user['id']) {
                throw new HttpNotFoundException($request);
            }

            $order['products'] = Products::fullList(['id_store_orders' => $order['id']]);

            if(!$order['shipping'] = Shipping::getLast($order['id'])) {
                $order['shipping'] = null;
              }

            //Di default torna get
            return $this->response($response,$order);
        }


        //Modalità lista
        if(!$data = Orders::list(['id_users' => $this->user['id'],'status:not' => 'cart'])) {
            $data = [];
        }

        return $this->response($response,$data);

    }

    */

}
