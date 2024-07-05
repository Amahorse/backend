<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\App;
use Kodelines\Tools\Json;
use RuntimeException;

class Translations {

/**
 * Ritorna array contenuto file in cartella lingua principale facendo merge su sotto ambiente app se trova file corrispondente
 *
 * @param string $file
 * @param bool $check se a true e non trova file torna false
 * @return array|bool
 */
public static function getFile(string $file, $check = false): array|bool {

   $translations = [];

   //Prima controllo file core 
   if($file_content = Json::arrayFromFile(_DIR_SRC_ . 'i18n/' . App::getInstance()->language . '/' . $file . '.json')) {

    $translations = $file_content;

   }

    //Poi controllo file <pplicazione che se esiste sovrascrive
    if($file_content = Json::arrayFromFile(_DIR_LANGUAGES_ . '/' . App::getInstance()->language . '/' . $file . '.json')) {

      $translations = array_merge($translations,$file_content);

    } 

    //Controllo check e array vuoto
    if(empty($translations) && $check) {
      return false;
    }


  return $translations;

}

/**
 * Aggiunge file ad array traduzioni, il file deve essere dentro cartella i18n principale o applicazione
 *
 * @param string $file
 * @return boolean
 */
  public static function addFile(string $file):bool {

    if(!$vars = self::getFile($file,true)) {
      throw new RuntimeException('Translation file "' . $file . '.json" not found or not well formatted');
    }

    return self::addVars($vars);
  }


  /**
   * Aggiunge variabili alle traduzioni della app facendo merge, quindi se presenti con stessa stringa vengono sovrascritte
   *
   * @param array $vars
   * @param bool  $override sovrascrive le variabili se true altrimenti le aggiunge
   * @return boolean
   */
  public static function addVars($vars = [], $override = true):bool {

    if($override) {
      App::getInstance()->translations = array_merge(App::getInstance()->translations,$vars);
    } else {
      App::getInstance()->translations = array_merge($vars,App::getInstance()->translations);
    }
  	
    return true;

  }


  /**
   * Aggiunge traduzioni da un file path
   *
   * @param string $path
   * @return boolean
   */
  public static function addPath(string $path):bool {

      //Poi controllo file <pplicazione che se esiste sovrascrive
      if($file_content = Json::arrayFromFile($path)) {

        self::addVars($file_content);
  
        return true;
    
      } 

      return false;

  }

  /**
   * Aggiunge traduzioni da un file path
   *
   * @param string $path
   */
  public static function getPath(string $path):array|bool {

    //Poi controllo file <pplicazione che se esiste sovrascrive
    if(!$file_content = Json::arrayFromFile($path)) {
      return true;  
    } 

    return $file_content;

  }


}

?>