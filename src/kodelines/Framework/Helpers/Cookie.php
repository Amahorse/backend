<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Tools\Json; 
use Kodelines\Tools\Str;

class Cookie {

  /**
   * Var to containe singleton instance
   *
   * @var object
   */
  protected static $instance = null;

  /**
   * Get singleton class instance
   *
   * @method getInstance
   * @return object      return var $instance
   */
  public static function getInstance() {  if(self::$instance == null) { self::$instance = new Cookie(); } return self::$instance; }

  protected $key = null;

  protected $https = false;

  protected $anonym = true;

  protected $domain = '';

  /**
   * Constructor checks the key, domain and connection type
   *
   */
  public function __construct() {


    
    //Check https forward
    $this->https = config('app','protocol');
    
    //Define cookie domain

    if(!empty($_SERVER['HTTP_HOST'])) {
      $this->domain = self::normalizeDomain($_SERVER['HTTP_HOST']);
    }

  }

  
  /**
   * Get real name of a cookie based on development mode or cookie key defined
   *
   * @return String
   */

  public function realName($name) {

    //TODO: questo sistema non è mai andato ne in produzione ne in beta, passare ad altro
    return $name;

    //App Development mode does not set encoded cookies
    if(dev() || $this->anonym === false) {
      return $name;
    }

    //Key null will set only shaq name
    if($this->key == null) {
      return sha1($name);
    }

    return sha1($this->key . $name);
  }


  /**
   * Check if Cookie exists
   *
   * @return Bool
   */

  public function exists($name) {
  	  return isset($_COOKIE[$this->realName($name)]);
  }


  /**
   * Check if Cookie is empty
   *
   * @return Bool
   */
  public function isEmpty($name) {

    if(!$this->exists($name)) {
      return false;
    }

    return empty($_COOKIE[$this->realName($name)]);
  }


  /**
   * Get a Cookie Value
   *
   * @return Mixed
   */

  public function get($name) {

  	if(!$this->exists($name)) {
      return false;
    }

    $value = $_COOKIE[$this->realName($name)];

    if($array = Json::arrayFromText($value)) { 
      $value = $array;
    } else {
      $value = urldecode($value);
    }

  return $value;
  }


  /**
   * Get a Cookie Value
   *
   * @return Bool
   */
  public function set($name, $value, $expiry = 0) {

    if (!headers_sent()) {

      $expiry = self::expires($expiry);

      //Json convert array
      if(is_array($value)) {
        $value = json_encode($value);
      } else if(!empty($value)) {
        $value = urlencode($value);
      } 

       $options = array(
         'path' => '/',
         'expires' => $expiry,
         'secure' => $this->https,
         'samesite' => 'Lax'
       );

       return setcookie($this->realName($name), $value, $options);

    }

  return false;
  }


  //KILL COOKIE
  public function delete($name) {
    return $this->set($name,'',time()-10000);
  }

  //Destriy all cookies
  public function destroy($httponly = true) {
    foreach($_COOKIE AS $key => $value) {
      $this->delete($key);
    }
  }


    /**
     * Normalize the cookie domain
     *
     * @return Mixed
     */
    private static function normalizeDomain($domain = null) {
		// make sure that the domain is a string
		$domain = (string) $domain;
		// if the cookie should be valid for the current host only
		if ($domain === '') {
			// no need for further normalization
			return null;
		}
		// if the provided domain is actually an IP address
		if (filter_var($domain, FILTER_VALIDATE_IP) !== false) {
			// let the cookie be valid for the current host
			return null;
		}
		// for local hostnames (which either have no dot at all or a leading dot only)
		if (strpos($domain, '.') === false || strrpos($domain, '.') === 0) {
			// let the cookie be valid for the current host while ensuring maximum compatibility
			return null;
		}
		// unless the domain already starts with a dot
    // TODO: questo creava casino perchè settava cookie con . davanti e altri senza
    /*
		if ($domain[0] !== '.') {
			// prepend a dot for maximum compatibility (e.g. with RFC 2109)
			$domain = '.' . $domain;
		}
    */

    //TODO: rimuovo sempre il punto, in questo modo il cookie è sempre per il dominio singolo, da rimuovere e fare altro in caso di cross site cookie
    if(Str::startsWith($domain,'.')) {
      $domain = substr($domain, 1);
    }

		// return the normalized domain
		return $domain;
	}


	/**
	 * Converte una stringa testuale in timestamp o lascia correre se non è stringa
	 *
	 * @param string|integer $expiry
	 * @return integer
	 */
	public static function expires(string|int $expiry): int {

		if(!is_numeric($expiry)) {

    		switch(mb_strtolower($expiry)) {
      		case 'session': $expiry = 0; break; //only for this session
      		case 'oneday': $expiry = 86400;  break;
      		case 'sevendays': $expiry = 604800; break;
      		case 'thirtydays': $expiry = 2592000; break;
      		case 'sixmonths': $expiry = 15811200; break;
      		case 'oneyear': $expiry = 31536000; break;
      		case 'lifetime': $expiry = 1893456000; break; // 2030-01-01 00:00:00
      		default: $expiry = 0;
    		}

      }

		//float limit Overflow Fix
		if($expiry <> 0) {

		$expiry = time() + $expiry;

			if($expiry > 2147483646) {
				$expiry = 2147483646;
			}

		}

	  return $expiry;

	}



}


?>