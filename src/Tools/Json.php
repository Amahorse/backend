<?php

declare(strict_types=1);

namespace Kodelines\Tools;

use Kodelines\Error;

class Json
{

	/**
	 * Convert json file in php array, return false if file not exists or is not a valid json
	 * TODO: questo dovrebbe tornare array [] o null con coalesce, trovare su tutto il codice e rimpiazzare tipo $original = Json::arrayFromFile(Models::folder() . $model . '/i18n/' . $this->cms->language . '.json') ?? [];
	 * @param string $file absolute path of the file
	 * @return mixed
	 */
	public static function arrayFromFile(string $file) :mixed
	{

		if (!file_exists($file) || !$content = file_get_contents($file)) {
			return false;
		}

		if (!$array = json_decode($content, true)) {
			return false;
		}

		return $array;
	}

	/**
	 * Convert json text in php array, return false if not a valid json
	 * 
	 * @param string $text well formatted json format
	 * @return mixed
	 */
	public static function arrayFromText(string $text) :mixed
	{	

		if (!$array = json_decode($text, true)) {
			return false;
		}

		return $array;
	}

	/**
	 * Put a php array inside json file
	 *
	 * @method arrayToFile
	 * @param  array       $array
	 * @param  string      $file
	 * @return bool
	 */
	public static function arrayToFile(array $array, string $file): int|bool
	{

		if ($json = stripslashes(json_encode($array, JSON_PRETTY_PRINT))) {
			return file_put_contents($file, $json);
		}

		return false;
	}

	/**
	 * Fa encode del json ma con i vari fix per numeric check, throw errori etc
	 *
	 * @param mixed $data
	 * @return string
	 */
	public static function encode(mixed $data): string
	{

	
		//This fix the problem of first 0 on numeric value forJSON NUMERIC CHECK
		if(!dev()) {

			//Il non sviluppo ritorna json compresso
			if(!$parsed = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) { 
				throw new Error('Json encode error:' . json_last_error());
			}

		} else {

			if(!$parsed = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) { 
				throw new Error('Json encode error:' . json_last_error());
			}

		}


		/** FIX NUMERIC CHECK CHE TOGLIE GLI ZERI */
		/*
		if(!$nonnumeric = json_encode($data)) { 
			throw new Error('Json encode error:' . json_last_error());
		}
		
		preg_match_all("/\"[0\+]+(\d+)\"/",$nonnumeric, $vars);

		foreach($vars[0] as $k => $v){
			$parsed = preg_replace("/\:\s*{$vars[1][$k]},/",": {$v},",$parsed);
		}
		*/

		return $parsed;

	}
}

?>