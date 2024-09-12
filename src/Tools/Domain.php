<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Domain {


  /**
   * Check and get domain protocol
   *
   * @method isHttps
   * @return string
   */
  public static function protocol(): string {

    if(isset($_SERVER['HTTPS']) || (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && $_SERVER['HTTP_X_FORWARDED_PORT'] == 443)) {
      return 'https';
    }

    return 'http';
  }


  /**
   * Get current domain if not isset or is ip return null
   *
   * @return string
   */
  public static function current(): string {

    if(!empty($_SERVER['HTTP_HOST']) && !filter_var($_SERVER['HTTP_HOST'],FILTER_VALIDATE_IP)) {

      return $_SERVER['HTTP_HOST'];

    }

    return 'localhost';
  }

  /**
   * Ritorna se il dominio è sicuro https
   *
   * @return boolean
   */
  public static function isSecure():bool {
    return self::protocol() === 'https';
  }

  /**
   * Rimuove trailing slash dal dominio
   *
   * @param string $domain
   * @return string
   */
  public static function removeTrailingSlash(string $domain):string {

    if(Str::endsWith($domain,'/')) {
      $domain = mb_substr($domain,0,-1);
    }

    return $domain;
  }


}