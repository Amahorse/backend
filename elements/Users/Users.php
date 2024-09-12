<?php

/**
 * @author Giordano Pierini <giordanopierini@gmail.com>
 * @category Core Models
 * @version 1.0
 * 
 */

declare(strict_types=1);

namespace Elements\Users;

use Kodelines\Abstract\Decorator;

class Users extends Decorator {

  /**
   * Genera username casuale
   *
   * @return string
   */
  public static function generateUsername():string {
    return uniqid('USER');
  }


  /**
   * Controlla se utente può modificarne un'altro o se stesso
   *
   * @param array $user
   * @return boolean
   */
  public static function canEdit(array $user):bool {

    if(!user() || ((int)$user['auth'] >= user('auth') && $user['id'] <> user('id'))) { 
      return false;
    }

    return true;

  }

  

 

}
