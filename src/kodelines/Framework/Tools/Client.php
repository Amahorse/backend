<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Client
{

  /**
   * Get current navigation IP v4
   * TODO: Questa classe è da rifare o da usare qualche libreria
   * @method getIP
   * @return string
   */
  public static function IP(): string
  {

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {

      return $_SERVER['HTTP_CLIENT_IP'];
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

      return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    if (isset($_SERVER['REMOTE_ADDR'])) {

      return $_SERVER['REMOTE_ADDR'];
    }

    return '127.0.0.1';
  }



	/**
	 * Get current browser language lowercase and 2chars
	 *
	 * @method language
	 * @return string|false
	 */
	public static function language(): string|false {

		if(empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			return false;
		}

		return mb_strtolower(mb_substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
	}


  
  /**
   * Ritorna origine della chiamata api o indirizzo server
   *
   * @return string
   */
  public static function origin(): string {

      if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
          return $_SERVER['HTTP_ORIGIN'];
      }
      
      if (array_key_exists('HTTP_REFERER', $_SERVER)) {
          return $_SERVER['HTTP_REFERER'];
      }
    
      return $_SERVER['REMOTE_ADDR'];
      
  }


  	/**
	 * Geta current browser Operative system
	 *
	 * @method getOS
	 * @return string
	 */
	public static function os() {

	if(PHP_SAPI == 'cli') {
		return 'Command Line';
	}

	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		return 'Unknown Browser';
	}

    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $os_platform = "Unknown OS Platform";

    $os_array = array(
		'/windows nt 11/i'     	=>  'Windows 11',
		'/windows nt 10/i'     	=>  'Windows 10',
		'/windows nt 6.3/i'     =>  'Windows 8.1',
		'/windows nt 6.2/i'     =>  'Windows 8',
		'/windows nt 6.1/i'     =>  'Windows 7',
		'/windows nt 6.0/i'     =>  'Windows Vista',
		'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
		'/windows nt 5.1/i'     =>  'Windows XP',
		'/windows xp/i'         =>  'Windows XP',
		'/windows nt 5.0/i'     =>  'Windows 2000',
		'/windows me/i'         =>  'Windows ME',
		'/win98/i'              =>  'Windows 98',
		'/win95/i'              =>  'Windows 95',
		'/win16/i'              =>  'Windows 3.11',
		'/macintosh|mac os x/i' =>  'Mac OS X',
		'/mac_powerpc/i'        =>  'Mac OS 9',
		'/linux/i'              =>  'Linux',
		'/ubuntu/i'             =>  'Ubuntu',
		'/iphone/i'             =>  'iPhone',
		'/ipod/i'               =>  'iPod',
		'/ipad/i'               =>  'iPad',
		'/android/i'            =>  'Android',
		'/blackberry/i'         =>  'BlackBerry',
		'/webos/i'              =>  'Mobile'
	);

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform    =   $value;
        }
    }

    return $os_platform;

	}

	/**
	 * Get current browser name
	 *
	 * @method get
	 * @return string
	 */
	public static function browser() {

		if(PHP_SAPI == 'cli') {
			return 'Command Line';
		}

		if(empty($_SERVER['HTTP_USER_AGENT'])) {
			return 'Unknown Browser';
		}

		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		if (strpos($user_agent, 'Opera') || strpos($user_agent, 'OPR/')) return 'Opera';
		elseif (strpos($user_agent, 'Edge')) return 'Edge';
		elseif (strpos($user_agent, 'Chrome')) return 'Chrome';
		elseif (strpos($user_agent, 'Safari')) return 'Safari';
		elseif (strpos($user_agent, 'Firefox')) return 'Firefox';
		elseif (strpos($user_agent, 'MSIE') || strpos($user_agent, 'Trident/7')) return 'Internet Explorer';

    	return 'Unknown Browser';

	}



}

?>