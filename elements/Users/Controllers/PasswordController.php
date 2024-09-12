<?php

 /**
  * @author Giordano Pierini <giordanopierini@gmail.com>
  * @copyright 2018 Kodelines
  * @category Admin Controllers
  * @version 0.1b
  */

  namespace Elements\Users\Controllers;

  
  use Kodelines\Oauth\Scope;
  use Kodelines\Db;
  use Kodelines\Key;
  use Kodelines\Mailer;
  use Kodelines\Abstract\Controller;
  use Slim\Exception\HttpUnauthorizedException;
  use Slim\Exception\HttpNotFoundException;
  use Kodelines\Exception\ValidatorException;
  use Kodelines\Helpers\Password;
  use Kodelines\Tools\Validate;
  use Elements\Users\Users;
  use Psr\Http\Message\RequestInterface as Request;
  use Psr\Http\Message\ResponseInterface as Response;

  class PasswordController extends Controller {

    /**
     * Modifica password utente 
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function update(Request $request, Response $response,array $args) : Response
    {


          if(!$user = Users::get(id($args['id']))) {
              throw new HttpNotFoundException($request);
          }
      
          //Controllo se utente corrente puà modificare quello con id selezionato
          if(!Users::canEdit($user)) {
              throw new HttpUnauthorizedException($request);
          }
  
          if(empty($this->data['password'])) {
            throw new ValidatorException('missing_password');
          }
  
          if(!isset($this->data['password_repeat'])) {
            throw new ValidatorException('missing_password_repeat');
          }
  
          if($this->data['password_repeat'] <> $this->data['password']) {
            throw new ValidatorException('password_different');
          }
  
          Password::create($this->data['password'],id($args['id']));
          
          //TODO: verificare queste email e fare spunta "Inviare mail a utente"

          //Mailer::queue('password-changed', $user['email'], $this->data,  $user['language'],3);
  
          return $this->response($response,true);
  

    }
   

    /**
     * Form di richiesta password dimenticata 
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function lost(Request $request, Response $response) : Response
    {
      

       if(!isset($this->data['email']) || !Validate::isEmail($this->data['email'])) {
         throw new ValidatorException('missing_email');
       }

       if(!$user = Users::where('email',$this->data['email'])) {
         throw new ValidatorException('user_not_found');
       }

       //Utente che si è già registrato ma non ha confermato
       if(Scope::is('banned')) {
         throw new ValidatorException('user_banned');
       }

       if(Scope::is('deleted')) {
         throw new ValidatorException('user_not_found');
       }



       //Utente che ha già effettuato registrazione ma non ha confermato lo rimando alla pagina di conferma
       if($user['auth'] == Scope::code('not_confirmed') || Scope::is('provisional')) {

         return $this->response($response,true);

       }


       $key = Key::build($user['id'],'password-recover',7);

       $user['link'] = $this->container->get('cms')->permalink->page('password-recover').'?id=' . $user['id'] .'&key='. $key;

       Mailer::queue('password-lost', $user['id'], $user, false,1);

      return $this->response($response,true);

    }


    /**
     * //Recuper password dimenticata 
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function recover(Request $request, Response $response,array $args) : Response
    {


      if(!isset($args['id']) || !isset($args['key']) || !Key::check($args['id'],$args['key'],'password-recover')) {
        throw new ValidatorException('key_not_valid_or_expired');
      }

      if(!$user = Users::get(id($args['id']))) {
        throw new ValidatorException('user_not_found');
      }

      //Utente che ha già effettuato registrazione ma non ha confermato
      if($user['auth'] == Scope::code('not_confirmed')) {
        throw new ValidatorException('user_not_confirmed');
      }

      //Utente che si è già registrato ma non ha confermato
      if($user['auth'] == Scope::code('banned')) {
        throw new ValidatorException('user_banned');
      }

      //Utente che ha iniziato checkoiyt ma non ha finito l'ordine o utente eliminato è come se non esistesse, lo aggiorno
      if($user['auth'] == Scope::code('provisional') || $user['auth'] == Scope::code('deleted')) {
        throw new ValidatorException('user_not_found');
      }

      if(!isset($this->data['password']) || empty($this->data['password'])) {
        throw new ValidatorException('missing_password');
      }

      if(!isset($this->data['password_repeat']) || empty($this->data['password_repeat'])) {
        throw new ValidatorException('missing_password_repeat');
      }

      if($this->data['password_repeat'] <> $this->data['password']) {
        throw new ValidatorException('password_different');
      }

      if(!Password::create($this->data['password'],$user['id'])) {
        throw new ValidatorException('password_error');
      }

  
    return $this->response($response,true);


  }

}

?>