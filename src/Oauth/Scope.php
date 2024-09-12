<?php

declare(strict_types=1);

namespace Kodelines\Oauth;

class Scope
{
 
  /**
   * [private description]
   *
   * @var [type]
   */
  private static $auths = [];


  public static function auths(): array {

    if(empty(self::$auths)) {
        self::$auths = config('auth');
    }

    return self::$auths;
  }

  /**
   * [list description]
   *
   * @method list
   * @param  boolean $exclude_before [description]
   * @return [type]                  [description]
   */
  public static function list($exclude_before = false) {

    $auths = self::auths();

    if($exclude_before) {

      foreach($auths as $key => $value) {
        if($key >= $exclude_before) {
          unset($auths[$key]);
        }
      }

    }

    return $auths;

  }

  /**
   * [name description]
   *
   * @method name
   * @param  [type] $auth [description]
   * @return [type]       [description]
   */
  public static function name(string|int $auth):string {

    if(isset(self::auths()[(int)$auth])) {
      return self::auths()[(int)$auth];
    }

  return $auth;
  }

  /**
   * [is description]
   *
   * @method is
   * @param  [type]  $name  [description]
   * @param  boolean $upper [description]
   * @return boolean        [description]
   */
  public static function is(string $name, $upper = false): bool {
   
    //Check for guest usr
    if($name == 'guest' && !user('auth')) {
      return true;
    }


    $auths = array_flip(self::auths());

    if(!isset($auths[$name])) {
      return false;
    }

    if(!$upper) {
      if(user('auth') && (user('auth') == $auths[$name])) {
         return true;
      }
    } else {
      if(user('auth') && (user('auth') >= $auths[$name])) {
         return true;
      }
    }


    return false;

  }


  /**
   * Da un codice autorizzazione ritorna un nome
   *
   * @param string $name
   * @return int
   */
  public static function code(string $name):int|bool {
  
    $auths = array_flip(self::auths());

    if(!isset($auths[$name])) {
      return false;
    }

    return (int)$auths[$name];
  }


}

?>