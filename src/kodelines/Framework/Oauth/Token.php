<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use DateTime;
use Throwable;
use InvalidArgumentException;
use Kodelines\Db;
use Kodelines\Key;
use Kodelines\Tools\Client as Browser;
use Firebase\JWT\JWT;


class Token
{

  /**
   * Client secret
   */
  private static $client_secret;

  /**
   * client id
   */
  private static $client_id;

  /**
   * kid
   */
  private static $kid = null;

  /**
   * Fa build di client id e client secret
   */
  public static function buildClient(string $client_id) {

    if(!Key::isValid($client_id)) {
      throw new InvalidArgumentException('client_id_not_valid');
    }

    //Client secret principale
    self::$client_secret = config('app','client_secret');

    //Prendo kid e client secret per sotto applicazioni
    if(!$secret = Client::check($client_id,true)) {
      throw new InvalidArgumentException('client_not_found');
    }

    self::$client_secret = $secret["client_secret"];

    self::$kid = $secret["kid"];

    self::$client_id = $client_id;

  }


    //REFRESH TOKEN: https://auth0.com/blog/refresh-tokens-what-are-they-and-when-to-use-them/

    /**
     * Get access token
     *
     * @param string $token
     * @return array|boolean
     */
    public static function getByAccess(string $token):array|bool {
      return Db::getRow("SELECT * FROM oauth_tokens WHERE access_token = " . encode($token));
    }

        /**
     * Get access token
     *
     * @param string $token
     * @return array|boolean
     */
    public static function getByRefresh(string $token):array|bool {
      return Db::getRow("SELECT * FROM oauth_tokens WHERE refresh_token = " . encode($token));
    }

    /**
     * Generate Token
     *
     * @param string $client_id
     * @param array $user
     * @param boolean $lifetime
     * @param boolean $database
     * @return array
     */
    public static function generate(string $client_id, $user = [], $lifetime = false, $database = true):array
    {

        self::buildClient($client_id);

        //DOC: guardare qui per autenticazione con kid
        //https://firebase.google.com/docs/auth/admin/verify-id-tokens?hl=it

        //DOC: Autenticazione server audience 
        //https://developer.okta.com/docs/guides/customize-authz-server/main/#testing-an-openid-connect-flow

        $now = new DateTime();

        if($lifetime) {

          $exp = 1893456000;
          
        } else {

          $future = new DateTime("now +" . config('token','expire_time'));

          $exp = $future->getTimeStamp();
        }

        if(!defined('_OAUTH_TOKEN_JTI_')) {
          $jti = bin2hex(openssl_random_pseudo_bytes(20));
        } else {
          $jti = _OAUTH_TOKEN_JTI_;
        }

        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $exp,
            "jti" => $jti,
            "aud" => self::$client_id,
            "alg" => config('token','algorithm'),
            'iss' => stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://' . $_SERVER['SERVER_NAME'] . '/oauth/token?client_id=' . $client_id,  // Issuer
            "sub" => !empty($user[config('token','identifier')]) ?  $user[config('token','identifier')] : null,
            "kid" => self::$kid,
            "scope" => !empty($user['auth']) ? Scope::name($user['auth']) : "guest"
        ];


        $access_token = JWT::encode($payload, self::$client_secret, config('token','algorithm'), self::$kid);

        $response = [
          "jti" => $jti,
          "access_token" => $access_token,
          "token_type" => "Bearer",
          "expires_in" => $exp - time(),
          "refresh_token" => Key::generate(),
          "scope" => $payload["scope"],
        ];

        //Se è già stato generato e passato un token lo sostituisco

        if($database) {

          $issuer = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
          
          if(defined('_OAUTH_TOKEN_JTI_')) {

            Db::updateArray('oauth_tokens',[
              'client_id' => self::$client_id,
              'access_token' => $access_token,
              "refresh_token" => $response["refresh_token"],
              'id_users' => !empty($user['id']) ? $user['id'] : null,
              'ip' => Browser::IP(),
              'scope' => $payload["scope"],
              'ua' => $_SERVER['HTTP_USER_AGENT'],
              'issuer' => $issuer,
            ],'jti',_OAUTH_TOKEN_JTI_);
  
          } else {
  
            Db::insert('oauth_tokens',[
              'jti' => $payload["jti"],
              'client_id' => self::$client_id,
              'access_token' => $access_token,
              "refresh_token" => $response["refresh_token"],
              'id_users' => !empty($user['id']) ? $user['id'] : null,
              'ip' => Browser::IP(),
              'scope' => $payload["scope"],
              'ua' => $_SERVER['HTTP_USER_AGENT'],
              'issuer' => $issuer
            ]);
  
          }
  

        }
        
     

        return $response;
    }

    /**
     * Refresh Token
     *
     * @param string $client_id
     * @param string $refresh_token
     * @return array|boolean
     */
    public static function refresh(string $client_id, string $refresh_token): array {

      self::buildClient($client_id);

      if(!$refresh = self::getByRefresh($refresh_token)) {
        throw new InvalidArgumentException('refresh_token_not_found');
      }
      
      if(!empty($refresh['id_users']) && !$user = User::checkCredentials($refresh['id_users'],'id','',false)) {
        throw new InvalidArgumentException('access_denied');
      }

      $now = new DateTime();

      $future = new DateTime("now +" . config('token','expire_time'));

      $exp = $future->getTimeStamp();

      $payload = [
        "iat" => $now->getTimeStamp(),
        "exp" => $exp,
        "jti" => $refresh["jti"],
        "aud" => self::$client_id,
        "alg" => config('token','algorithm'),
        'iss' => address() . '/oauth/token?client_id=' . $client_id,  // Issuer
        "sub" => !empty( $user[config('token','identifier')]) ?  $user[config('token','identifier')] : null,
        "kid" => self::$kid,
        "scope" => !empty($user['auth']) ? Scope::name($user['auth']) : "guest"
      ];

      $access_token = JWT::encode($payload, self::$client_secret, config('token','algorithm'), self::$kid);

      $response = [
        "access_token" => $access_token,
        "token_type" => "Bearer",
        "expires_in" => $exp - time(),
        "refresh_token" => $refresh_token,
        "scope" => $payload["scope"]
      ];

      Db::query("UPDATE oauth_tokens SET access_token = " . encode($access_token) . "WHERE jti = " . encode($refresh["jti"]));
    
      return $response;

    }

    /**
     * Controlla validità token dal database
     *
     * @param string $token
     * @return boolean
     */
    public static function isValid(string $token, string $client_id, $jwtCheck = false): array|bool
    {

      //Questo lo controlla solo, non fa throw errore
      if($jwtCheck) {

        try {

          Jwt::decode($token,config('app','client_secret'),[config('token','algorithm')]);

        } catch (Throwable $e) {

          return false;

        }

        Jwt::decode($token,config('app','client_secret'),[config('token','algorithm')]);
      }
     
      return Db::getRow("SELECT jti FROM oauth_tokens WHERE access_token = " . encode($token) . " AND client_id = " . encode($client_id));
    }


     /**
     * Controlla validità token dal database
     *
     * @param string $token
     * @return boolean
     */
    public static function revoke(string $token, string $client_id): array|bool
    {
      return Db::query("DELETE FROM oauth_tokens WHERE access_token = " . encode($token) . " AND client_id = " . encode($client_id));
    }
}

?>