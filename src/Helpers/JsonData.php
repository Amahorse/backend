<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Tools\Json;
use Kodelines\Tools\Folder;
use Kodelines\Tools\Str;

/**
 * Questa classe serve a gestire i json dentro la cartella uploads 
 */
class JsonData {

  /**
   * Lista dei json contenuti in cartella
   *
   * @return array
   */
  public static function list():array {

    $list = [];
    
    if(!$folder = Folder::read(_DIR_UPLOADS_.'json')) {
      return [];
    } 

    foreach($folder as $file) {
      if(Str::endsWith($file,'.json')) {
        $list[] = [
          'file' => $file,
        ];
      }
    }

    return $list;

  }

  /**
   * Prende contenuto del json in modalità array o testo
   *
   * @param string $file
   * @param string $mode
   * @return mixed
   */
  public static function get(string $file, $mode = 'array'):mixed {

    if($mode == 'array') {
      return Json::arrayFromFile(_DIR_UPLOADS_. 'json/' . $file);
    }

    return file_get_contents(_DIR_UPLOADS_. 'json/' .$file);
  }

  /**
   * Aggiorna il json controlando che sia correttamente formattato
   *
   * @param string $file
   * @param [type] $json
   * @return boolean
   */
  public static function update(string $file,$json):bool {

    if(!Json::arrayFromText($json)) {
      return false;
    }

    return (bool)file_put_contents(_DIR_UPLOADS_ . 'json/'. $file,$json);

  }




}

?>