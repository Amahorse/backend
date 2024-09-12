<?php

declare(strict_types=1);

namespace Kodelines;

use Kodelines\Db;
use Kodelines\Tools\Str;
use Exception;

class Key {

	/**
	 * Generate a 64 bit uniq salt
	 */

	public static function generate() {
		return Str::random(16) . "." . md5(uniqid("key-", true)) . "." . time() . Str::random(4);
	}

	/**
	 * Check if a key is valid
	 */

	public static function isValid($key) {

		$parts = explode(".", $key);

		try {

			if(!$parts = explode(".", $key)) {
				throw new Exception();
			}

			if(count($parts) !== 3) {
				throw new Exception();
			}

			if(strlen($parts[0]) !== 16) {
				throw new Exception();
			}

			//NOTE: potrebbe non funzionanre che non si sa se con la modifica è 32
			if(strlen($parts[1]) !== 32) {
				throw new Exception();
			}

			if(strlen($parts[2]) !== 14) {
				throw new Exception();
			}

			return true;

		} catch (Exception $e) {
			return false;
		}


	}

	/**
	 * Build a key and insert it to db for a certain action
	 */
	public static function build($id_users, $type, $days) {

		$key = self::generate();

		Db::query("REPLACE INTO users_keys (id_users,key_value,key_type,key_expiry) VALUES (".id($id_users).", ".encode($key).", ".encode($type).", DATE_ADD(NOW(),INTERVAL ".(int)$days." DAY)) ");

		return $key;
	}

	/**
	 * Check key on db
	 */
	public static function check($id_users, $key, $type) {

		if(!self::isValid($key) || !is_numeric($id_users)) {
			return false;
		}

		return Db::getRow("SELECT key_value FROM users_keys WHERE id_users = ".id($id_users)." AND key_value = ".encode($key)."  AND key_type = ".encode($type)." AND key_expiry > NOW() ");
	}

	/**
	 * Delete a key from db
	 */
	public static function delete($id_users, $key, $type) {

		if(!self::isValid($key) || is_numeric($id_users)) {
			return false;
		}

		return Db::getRow("DELETE FROM users_keys WHERE id_users = ".id($id_users)." AND key_value = ".encode($key)." AND key_type = ".encode($type)." ");
	}


}

?>