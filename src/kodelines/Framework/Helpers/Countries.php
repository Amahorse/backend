<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Db;
use Kodelines\Helpers\Cache;

class Countries {

  //TODO: mettere in cache tutto e get, getByShortName e getShortName vanno a controllare su array cache

  /**
   * Torna singola nazione
   *
   * @param integer $id
   * @return array|boolean
   */
  public static function get(int $id):array|bool {
    return Db::getRow("SELECT * FROM countries WHERE id = " . $id);
  }

  /**
   * Torna singola nazione per short name
   *
   * @param integer $id
   * @return array|boolean
   */
  public static function getByShortName(string $iso):array|bool {
    return Db::getRow("SELECT * FROM countries WHERE LOWER(iso) = " . encode(mb_strtolower($iso)));
  }

  /**
   * Torna singola nazione per short name
   *
   * @param integer $id
   * @return string|boolean
   */
  public static function getShortName(int $id):string|bool {
    return Db::getValue("SELECT iso FROM countries WHERE id = " . id($id));
  }


  /**
   * Get a list of items
   *
   * @method list
   * @return array Lista di nazioni, viene messa in cache per risparmiare risorse database siccome è chiamata spesso
   */
  public static function list():array {

    if(!$list = Cache::getInstance()->getArray('countries_list')) {

      $list = Db::getArray("SELECT * FROM countries ORDER BY country ASC ");

      Cache::getInstance()->setArray($list,'countries_list');
    }

    return $list;
  }



  /**
   * Ritorna lista nazioni per comunità
   *
   * @param string $community
   * @return array
   */
  public static function getByCommunity(string $community):array {
    return Db::getArray("SELECT * FROM countries WHERE community = " . encode($community));
  }

  /**
   * Return list of states by country
   *
   * @param integer $id_countries
   * @return array
   */
  public static function listStates(int $id_countries):array {
    return Db::getArray("SELECT * FROM countries_states WHERE id_countries = " . $id_countries . " ORDER BY state ASC");
  }

  /**
   * Torna stato per short name
   *
   * @param string $iso
   * @return array|boolean
   */
  public static function getStateByShortName(string $iso):array|bool {
    return Db::getRow("SELECT * FROM countries_states WHERE LOWER(state_short) = " . encode(mb_strtolower($iso)));
  }




    /**
   * Return list of states by country
   *
   * @param integer $id_countries
   * @return array
   */
  public static function listRegions(int $id_countries):array {
    return Db::getArray("SELECT * FROM countries_regions WHERE id_countries = " . $id_countries . " ORDER BY region ASC");
  }



  /**
   * Get phone prefix by country
   * TODO: questi possono essere presi anche da cache per evitare spreco di risorse
   * @param integer $id_countries
   * @return string|boolean
   */
  public static function getPhonePrefix(int $id_countries): string|bool {
    return Db::getValue("SELECT phone_prefix FROM countries WHERE id = " .$id_countries);
  }


  /**
   * Get zip code regexp by country
   *
   * @param integer $id_countries
   * @return string|boolean
   */
  public static function getZipRegexp(int $id_countries): string|bool {
    return Db::getValue("SELECT zip_code_regexp FROM countries WHERE id = " .$id_countries);
  }






}

?>