<?php

declare(strict_types=1);

namespace Kodelines\Abstract;

use Kodelines\Error;

/**
 * Se si estende una classe con questa, la classe avrà a disposizione tutti i metodi statici del modello che si chiama allo stesso modo
 * Ad esempio class Users extends Decorator
 * 
 */
abstract class Decorator {



/**
   * Contiene array di modelli istanziati dal decorator
   *
   * @var  array
   */
  public static $instances = [];


  public static function __callStatic($method, $args)
  {

    if(!$model = self::findModel(get_called_class())) {
      throw new Error('Decorator must extends a class with a valid associated model');
    }

    if (empty(self::$instances[$model])) {

      //Instanzia un modello
      self::$instances[$model] = new $model;

    }
    
    //Chiama la funzione ma con metodo statico 
    return call_user_func_array(array(self::$instances[$model], $method), $args);
  } 


  /**
   * Trova modello corrispondente alla classe
   *
   * @param string $class
   * @return string|boolean
   */
  public static function findModel(string $class): string|bool {

    $classParts = explode('\\', $class);

    $className = end($classParts);

    $counter = count($classParts) -1;

    $class = '';

    foreach($classParts as $key => $part) {

      if($key == $counter) {
        break;
      }

      $class .= '\\' . $part;

    }

    $class .= '\Models\\' . $className . 'Model'; 
  
    if(!class_exists($class)) {
      return false;
    }

    return $class;
  }

  /**
   * Get campi modello
   *
   * @param object $model
   * @return array
   */
  public static function getFields(object $model):array {
  
    if(!empty($model->fields)) {
      return $model->fields;
    }

    return [];

  }


}

?>