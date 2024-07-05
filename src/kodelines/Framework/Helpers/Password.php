<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Db;
use Kodelines\Exception\ValidatorException;
use Kodelines\Exception\RuntimeException;
use Kodelines\Tools\Str;

class Password {

	/**
	 * Opzioni di validazione password il valore va messo su array password di config.php
	 *
	 * @var array
	 */
	private static $options = [
		"all" => '^(*)$', //all
		"alphanumeric" => '^(?=.*[a-zA-Z0-9])$/', //alphanumeric
		"alphanumeric_required" => '^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]$', //1 Alphabet and 1 Number
		"alphanumeric_special" => '^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]$', //1 Alphabet, 1 Number and 1 Special Character
		"alphanumeric_uppercase" => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]$', //1 Uppercase Alphabet, 1 Lowercase Alphabet and 1 Number
		"alphanumeric_uppercase_special" => '^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&])[A-Za-z\d$@$!%*?&]' //1 Uppercase Alphabet, 1 Lowercase Alphabet, 1 Number and 1 Special Character:
	];


	/**
	 * Valida password per inserimento
	 *
	 * @param string $password
	 * @return boolean
	 */
	public static function validate(string $password):bool {

		$config = config('password');

		if(!isset(self::$options[$config['validate']])) {
			throw new RuntimeException('Password config "'.$config['validate']. '" not found');
		}

		if($config['validate'] <> 'all' && !preg_match(self::$options[$config['validate']], $password)) {
			throw new ValidatorException('password_'.$config['validate']);
		}

		if(mb_strlen($password) < $config['minimum_length']) {
			throw new ValidatorException('password_too_short');
		}

		if(mb_strlen($password) > $config['maximum_length']) {
			throw new ValidatorException('password_too_long');
		}

		return true;
	}

	/**
	 * Crea array con nuova password 
	 *
	 * @param boolean $password
	 * @return array
	 */
	public static function create(string $password = '', int $id_users = 0):array {

		$config = config('password');

		if(empty($password)) {
			$password = Str::random($config['minimum_length'] + 2);
		} else {
			self::validate($password);
		}

		//create random hash
		$hash = Str::random(10);

		$password = sha1($hash.$password);

		//se settato id utente aggiorna anche il database
		if(!empty($id_users)) {
			//Update database
			Db::query("UPDATE users SET password = ". encode($password).", hash = ".encode($hash)." WHERE id = " . $id_users);
		}

		return array('hash' => $hash, 'password' => $password);
	}


	/**
	 * Controlal la password su tabella utente con hash
	 *
	 * @param string $hash
	 * @param string $password
	 * @param integer $id
	 * @return boolean
	 */
	final static public function check(string $hash, string $password, int $id):bool {

		$password = sha1($hash.$password);

		if(!Db::getValue("SELECT id FROM users WHERE id = " . $id. " AND password = " . encode($password))) {
			return false;
		}

		return true;
	}

}
?>