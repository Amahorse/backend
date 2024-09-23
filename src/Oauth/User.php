<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

use Kodelines\Db;
use Elements\Users\Users;
use Kodelines\Helpers\Password;

class User
{
 
    /**
     * Controlla credenziali utente
     *
     * @param mixed $identifier
     * @param string $password
     * @param boolean $check
     * @return void
     */
    public static function checkCredentials(mixed $identifier, string $sub, string $password, $check = true)
    {
       
        if(is_string($identifier)) {
          $identifier = mb_strtolower($identifier);
        }


        if(!$user = Users::where($sub, $identifier)) {
          return false;
        }
    
        //Controllo livello autorizzazione minimo per accesso alla app
        if(!in_array(Scope::name($user["scopes"]),config('token','scopes'))) {      
          return false;
        }
   

        if($check && !Password::check($user['hash'],$password,$user['id'])) {
            return false;
        }

        return $user;
    }


    /**
     * Controlla se utente è valido
     *
     * @param array $token
     * @return array|boolean
     */
    public static function isValid(array $token): array|bool
    {

        if(empty($token['decoded'])) {
            return false;
        }

        if(empty($token['decoded']['sub'])) {
          return false;
        }

        return Db::getRow(Users::query([config('token','identifier') => $token['decoded']['sub'], 'token' => $token["token"], 'client_id' => $token['decoded']['aud']]));
    }


}

?>