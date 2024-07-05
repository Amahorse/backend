<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use Kodelines\Error;
use Kodelines\Tools\Folder;

//TODO: implementare su PSR interface standard \Psr\SimpleCache\

class Cache {

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
	public static function getInstance() {  if(self::$instance == null) {self::$instance = new Cache;} return self::$instance;}

	/**
	 * If cache directory is not defined with a constant will cause system critical error
	 *
	 * @method __construct
	 */
	protected function __construct() {

		if(!defined('_DIR_CACHE_')) {
			throw new Error('Cache Directory not defined');
		}

		//Faccio un controllo che svuota cache se non trova stessa versione della app
		if(config('app','cache') && !$this->getFile(config('app','version'))) {

			$this->clear();

			$this->setFile(config('app','version'),'OK');
		}

	}

	/**
	 * Check if a file exists in cache directory
	 *
	 * @method fileExists
	 * @param  string     $file cache file name without extendsion
	 * @return boolean
	 */
	public function fileExists(string $file):bool {

		$file = _DIR_CACHE_ . md5($file) . '.cache';

		if($timing = @filemtime($file)) {

			/* Cache is valid for a day */
			if(time() - $timing >= config('app','cache_expire_time')) {
				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Get a file content if exists, return false if not
	 *
	 * @method getFile
	 * @param  string     $name cache file name without extendsion
	 * @return mixed
	 */
	public function getFile(string $name) {

		$cache_file = _DIR_CACHE_ .  md5($name).'.cache';

		//check if file exits or is expired
		if(!$this->fileExists($name) || config('app','cache') == false) {
			return false;
		}

	return file_get_contents($cache_file);
	}

	/**
	 * Set a file content
	 *
	 * @method setFile
	 * @param  string     $file cache file name without extendsion
	 * @param  string     $content the content of file
	 * @return boolean
	 */
	public function setFile(string $file, string $content) {
		return file_put_contents(_DIR_CACHE_ . md5($file) . '.cache' ,$content);
	}

	/**
	 * Remove a file from directory cache
	 *
	 * @method removeFile
	 * @param  string     $file cache file name without extendsion
	 * @return boolean
	 */
	public function removeFile(string $file) {
		return @unlink(_DIR_CACHE_ . md5($file) . '.cache');
	}

	/**
	 * Set a array in cache inside a file
	 *
	 * @method setArray
	 * @param  array      $array the content of file
	 * @param  string     $name cache file name without extendsion
	 */
	public function setArray(array $array, string $name) {
		return file_put_contents(_DIR_CACHE_ . md5($name).'.cache', serialize($array));
	}

	/**
	 * Get a file content array if exists, return false if not
	 *
	 * @method getArray
	 * @param  string     $name cache file name without extendsion
	 * @return mixed
	 */
	public function getArray(string $name): bool|array {

		$cache_file = _DIR_CACHE_ .  md5($name).'.cache';

		//check if file exits or is expired
		if(!$this->fileExists($name) || config('app','cache') == false) {
			return false;
		}

		return unserialize(file_get_contents($cache_file));
	}


	/**
	 * Pulisce tutta la cache
	 *
	 * @return void
	 */
	public static function clear() {
		return Folder::clear(_DIR_CACHE_,true);
	}


}

?>