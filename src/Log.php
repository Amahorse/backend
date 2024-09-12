<?php

/**
 * TODO: funzione che pulisce tutti i log leggendo cartella e generando file vuoti
 * TODO: documentazione 
 */

declare(strict_types=1);

namespace Kodelines;

use Kodelines\Tools\Client;
use Kodelines\Tools\File;
use Kodelines\Exception\RuntimeException;

class Log
{


    
  /**
   * Scrive un nuovo log xml dentro la cartella _DIR_LOGS_
   *
   * @param  string $type
   * @param  mixed $log
   * @param  mixed $custom
   * @return void
   */
  public function __construct(string $type, $log, $custom = [])
  {

    if(config('logs', $type) === true) {

      if(!empty($_SERVER['REQUEST_URI'])) {
        $from = $_SERVER['REQUEST_URI'];
      } elseif(!empty($_SERVER['argv'])) {
        $from = serialize($_SERVER['argv']);
      } else {
        $from = 'Unknown';
      }

      $xml = '<' . $type . '><instanceid>' . _SYSTEM_INSTANCE_ID_ . '</instanceid><date>' . _NOW_ . '</date><uri>' . $from . '</uri><ip>' . Client::IP() . '</ip><log>' . $log . '</log>';
      foreach ($custom as $key => $value) {

        if(is_array($value)) {
          $value = serialize($value);
        }

        $xml .= '<' . $key . '>' . $value . '</' . $key . '>';
      }
      $xml .= '</' . $type . '>' . _RN_;

      $log_file = config('dir', 'logs') . $type . '.log';
      
      //Se il file è troppo grande lo elimina e ne ricrea uno nuovo subito dopo
      if (File::size($log_file) > 100000) {
        @unlink($log_file);
      }

      //ATTENZIONE: questa exception se creata può generare loop perchè logga anche dentro runtime
      if(!File::write($log_file, $xml)) {
        throw new RuntimeException("File ".$log_file ." is not writeable");
      }

    }
  }
}
