<?php

declare(strict_types=1);

/* 
 * Token
 * 
 * Classe per la gestione dei token
 * 
 * @package Kodelines\Oauth
 * 
 * NOTE: attualmente il jti non cambia mai e viene generato una sola volta per client. 
 * Quindi se un client genera un token e lo rinnova oppure si fa login/logout, il jti rimane lo stesso anche per clienti diversi
 * 
*/

namespace Kodelines\Oauth;

use DateTime;

use Kodelines\Context;
use Kodelines\Key;
use Kodelines\Db;
use Kodelines\Tools\Domain;
use Firebase\JWT\JWT;
use Slim\Psr7\Request;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;

class Token
{

  private array $parameters;

  public array $client;

  public array|false $user;

  public array $payload;

  public string $token;

  private Request $request;
  
  /**
   * Costruttore
   *
   * Gli fa passato il token processato dal middleware
   * 
   * @param array $arguments
   * @return void
   */
  public function __construct(Request $request, array $arguments = []) {

    $this->request = $request;

    $this->parameters = Context::$parameters['token'];

    if(!empty($arguments['client_id'])) {
      return $this->generate($arguments['client_id']);
    }

    if(!empty($arguments['token'])) {
      return $this->process($arguments);
    }

  }

  public function generate(string $client_id): void{

    if (!Key::isValid($client_id)) {
      throw new HttpBadRequestException($this->request,'client_id_not_valid');
    }

    if (!$this->client = Client::get($client_id)) {
      throw new HttpBadRequestException($this->request,'client_not_found');
    }

    $this->payload = $this->createPayload();

    $this->token = JWT::encode($this->payload, $this->client['client_secret'], $this->parameters['algorithm'], $this->client['kid']);

    //Inserisco nel db solo se l'utente è presente
    if(!empty($this->user)) {

      $database = [
        'client_id' => $this->client['client_id'],
        'access_token' => $this->token,
        'refresh_token' => Key::generate(),
        'id_users' => $this->user['id'],
        'role' => $this->payload['role'],
        'scope' => $this->payload['scope'],
        'issuer' => $this->payload['iss'],
        'jti' => $this->payload['jti']
      ];

      if(defined('_OAUTH_TOKEN_JTI_')) {
        Db::replace('oauth_tokens',$database);
      } else {
        Db::insert('oauth_tokens',$database);
      }

    }

  }

  public function process(array $arguments) {

    if(empty($arguments['decoded'])) {
      throw new HttpBadRequestException($this->request,'token_not_decoded');
    }

    if(empty($arguments['decoded']['jti']) || empty($arguments['decoded']['aud'])) {
      throw new HttpBadRequestException($this->request,'token_not_valid');
    }

    /**
     * Definisco il token inviato come costante
     */
    define('_OAUTH_TOKEN_',$arguments["token"]);

    /**
     * Definisco il token jti
     */
    define('_OAUTH_TOKEN_JTI_',$arguments['decoded']['jti']);

    /**
     * Definisco il client id e controllo il client secret
     */
    define('_OAUTH_CLIENT_ID_',$arguments['decoded']['aud']);


    if (!$this->client = Client::get($arguments['decoded']['aud'])) {
      throw new HttpBadRequestException($this->request,'client_not_found');
    }

    $this->payload = $arguments['decoded'];

    $this->token = $arguments["token"];

    //Controllo utente
    if(!empty($arguments['decoded']['sub'])) {
      $this->checkAuth();
    }

  }

  private function createPayload($lifetime = false): array
  {
    
    $now = new DateTime();

    $exp = $lifetime ? 1893456000 : (new DateTime("now +" . $this->parameters['expire_time']))->getTimeStamp();
    
    $jti = defined('_OAUTH_TOKEN_JTI_') ? _OAUTH_TOKEN_JTI_ : bin2hex(openssl_random_pseudo_bytes(20));

    return [
      "iat" => $now->getTimeStamp(),
      "exp" => $exp,
      "jti" => $jti,
      "aud" => $this->client['client_id'],
      "alg" => $this->parameters['algorithm'],
      'iss' => Domain::protocol() . $_SERVER['SERVER_NAME'] . '/oauth/token?client_id=' . $this->client['client_id'],
      "sub" => !empty($this->user[$this->parameters['identifier']]) ? $this->user[$this->parameters['identifier']] : null,
      "kid" => $this->client['kid'],
      "role" => !empty($this->user['role']) ? $this->user['role'] : $this->parameters['role'],
      "scope" => !empty($this->user['scope']) ? $this->user['scope'] : $this->parameters['scope'],
    ];
  
  }


  public function createResponse(): array
  {

    return [
      "jti" => $this->payload['jti'],
      "access_token" => $this->token,
      "token_type" => "Bearer",
      "expires_in" => $this->payload['exp'] - time(),
      "refresh_token" => Key::generate(),
      "scope" => $this->payload['scope'],
      "role" => $this->payload['role']
    ];

  }

  
  public function checkAuth()
  {
    //TODO: controllare role utente
    //TODO: fare authInterceptor per pannello che butta fuori l'utente non autorizzato, trovare il modo di distinguere i vari errori bad request o unauthorized
    if(!$this->user = Db::getRow("SELECT users.* FROM oauth_tokens JOIN users ON oauth_tokens.id_users = users.id WHERE oauth_tokens.client_id = ".encode($this->client['client_id'])." AND oauth_tokens.access_token = " . encode($this->token))) {
      throw new HttpUnauthorizedException($this->request);
    }
  }

  public static function revoke(string $token, string $client_id): array|bool
  {
    return Db::query("DELETE FROM oauth_tokens WHERE access_token = " . encode($token) . " AND client_id = " . encode($client_id));
  }

}
?>
