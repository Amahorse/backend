<?php

/**
 * Classe speciale che può essere istanziata solo una volta in tutto il sistema
 */

declare(strict_types=1);

namespace Kodelines;

class System
{

  /**
   * Constant with system version
   *
   * @const string
   */
  const VERSION = '2.0';

  /**
   * Start the system defining folders and fixers, can be called only once
   */
  public static function start() {

    //l'id istanza serve ad avere un solo start e a distinguere errori a cascata nei log
    if(defined('_SYSTEM_INSTANCE_ID_')) {
      throw new Error('System already started');
    }

    //Definisco subito id istanza
    define('_SYSTEM_INSTANCE_ID_',uniqid(microtime(), true));

    //Definisco subito tutti i charset a utf8
    ini_set('default_charset','UTF-8');
    mb_internal_encoding("UTF-8");
    mb_http_output("UTF-8");
    

    //Define script time start
    define('_TIMESTART_',microtime(true));

    //Define document Root 
    define('_DIR_ROOT_',findRoot());

    ////////////////
    //MAIN FOLDERS// 
    ////////////////

    define('_FOLDER_VENDOR_',  'vendor/');
    define('_FOLDER_SRC_', 'src/');
    define('_FOLDER_LOGS_', 'logs/');
    define('_FOLDER_PUBLIC_', 'public/');
    define('_FOLDER_CACHE_', 'cache/');
    define('_FOLDER_TEMP_', 'temp/');
    define('_FOLDER_CRON_', 'cron/');
    define('_FOLDER_CONFIG_', 'config/');
    define('_FOLDER_FRAMEWORK_', 'kodelines/');
    define('_FOLDER_CONTROLLERS_', 'controllers/');
    define('_FOLDER_LANGUAGES_', 'i18n/');

    //absolute paths for main folders
    define('_DIR_VENDOR_', _DIR_ROOT_ . _FOLDER_VENDOR_);
    define('_DIR_SRC_', _DIR_ROOT_ . _FOLDER_SRC_);
    define('_DIR_LOGS_',  _DIR_ROOT_ .  _FOLDER_LOGS_);
    define('_DIR_PUBLIC_',  _DIR_ROOT_ .  _FOLDER_PUBLIC_);
    define('_DIR_CACHE_',  _DIR_ROOT_ .  _FOLDER_CACHE_);
    define('_DIR_TEMP_',  _DIR_ROOT_ .   _FOLDER_TEMP_);
    define('_DIR_CONFIG_',  _DIR_ROOT_ .   _FOLDER_CONFIG_);
    define('_DIR_FRAMEWORK_', _DIR_SRC_ . _FOLDER_FRAMEWORK_);

    //Folder uploads is public 
    define('_DIR_UPLOADS_', _DIR_PUBLIC_ . 'cdn/');
    define('_DIR_CONTROLLERS_', _DIR_FRAMEWORK_ . _FOLDER_CONTROLLERS_);
    define('_DIR_LANGUAGES_', _FOLDER_LANGUAGES_);
    

    //DEFINE STRING HELPERS 
    define('_RN_',"\r\n"); //Return
    define('_TB_',"\t"); //Tab

    //DEFINE DATE HELPERS
    define('_TODAY_',date('Y-m-d'));
    define('_NOW_', date("Y-m-d H:i:s"));

    /*Define Charset*/
    ini_set('default_charset','UTF-8');

    mb_internal_encoding("UTF-8");

    mb_http_output("UTF-8");

    $_ENV['config'] = new Config();

  }
  
}
