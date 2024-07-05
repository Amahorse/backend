<?php

/**
 * Classe speciale che può essere istanziata solo una volta in tutto il sistema che contiene variabili e istanze globali definite da config, server o headers
 * L'istanza viene inizializzata con valori base nel container di ogni context e poi riempita dai vari middleware o container
 */

declare(strict_types=1);

namespace Kodelines;

use Kodelines\System;
use Kodelines\Tools\Domain;
use Context\Admin\Factory as Admin;
use Context\Front\Factory as Front;
use Context\Api\Factory as Api;
use Context\Cli\Factory as Cli;
use Slim\App as Slim;


class App
{

  /** 
   * App domain
   */
  public $domain;

  /** 
   * App address
   */
  public $address;

  /** 
   * App language
   */
  public $language;

  /** 
   * App context (Caretella dove è contenuta la app)
   */
  public $context;

  /**
   * Var to store app translations
   *
   * @var array
   */
  public $translations = [];

  /**
   * Var to store app translations
   *
   * @var array
   */
  public $messages = [];

  /**
   * Var to store app request data from ApiMiddleware
   *
   * @var object|null
   */
  public $requestData = [];


  /**
   * Var to store the config when loaded
   *
   * @var object|null
   */
  public $config;

  /**
   * Var to store current user vars retrieved by token
   *
   * @var object|null
   */
  public $user = [];


  /**
   * Var to store current user vars retrieved by token
   *
   * @var object|null
   */
  public $locale = [];


  /**
   * Var to store current client vars retrieved by token
   *
   * @var object|null
   */
  public $client = [];


  /**
   * Undocumented variable
   *
   * @var object
   */
  public object $cart;


  /**
   * Var to containe singleton instance
   *
   * @var object
   */
  protected static $instance = null;

  /**
   * is not allowed to call from outside to prevent from creating multiple instances,
   * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
   */
  private function __construct()
  {

    //Start system 
    if(!defined('_SYSTEM_INSTANCE_ID_')) {
      System::start();
    }
    
  }


  /**
   * Buildare una app è come una istanza singleton che ritorna se stessa ma può chiamare solo dispatcher o metodi singoli
   *
   * @method getInstance
   * @return object      return var $instance
   */
  public function build(): object
  {


    //Recupero configurazioni app 
    $this->config = new Config(['strict' => true, 'domain' => $this->domain]);
 
    $this->address = config('app','protocol') . '://' . $this->domain;

    //Questo va fatto ora perchè il dominio può avere debug mode diversi
    if (dev()) {

      error_reporting(E_ALL);

      ini_set('display_errors', '1');

    } else {

      error_reporting(0);

      ini_set('display_errors', '0');
    }
 
    return $this;
  }

  public function cli() {

    if (PHP_SAPI !== 'cli') {
      die('Must run as a CLI application' . _RN_);
    }

    $this->domain = !empty($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : die('You must set domain to run over CLI' . _RN_);


  }

  /**
   * Dispatch api url 
   *
   * @return object
   */
  public function api() {

    if (PHP_SAPI == 'cli') {
      die('Must run as a HTTP application');
    }

    $this->domain = Domain::current();

  }


  /**
   * Get singleton app instance
   *
   * @method getInstance
   * @return object      return var $instance
   */
  public static function getInstance(): object
  {

    if (self::$instance === null) {

      self::$instance = new App;

    }

    return self::$instance;
  }



}
