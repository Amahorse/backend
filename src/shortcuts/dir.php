<?php 

declare(strict_types=1);

use Kodelines\App;

if (!function_exists('findRoot')) {

  /**
   * Trova la root del sistema. funziona solo se messo dentro cartella apps/
   *
   * @return string
   */
  function findRoot():string {

    $split = explode("public",getcwd());

    return $split[0];

    }

}


if (!function_exists('uploads')) {
  /**
   * Shortcut to get uploads directory
   *
   * @return string
   */
 function uploads(): string
  {
    return App::getInstance()->config->get('dir','uploads');
  }

}

?>