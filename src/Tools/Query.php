<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Query
{


	/**
	 * Add var to query string
	 *
	 * @param string $url
	 * @param string $key
	 * @param mixed $value
	 * @return string
	 */
	public static function addVar(string $url, string $key, mixed $value): string
	{

		$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');

		$url = mb_substr($url, 0, -1);

		if (mb_strpos($url, '?') === false) {
			return ($url . '?' . $key . '=' . $value);
		} else {
			return ($url . '&' . $key . '=' . $value);
		}
	}

	/**
	 * Remove var from query string
	 *
	 * @param string $url
	 * @param string $key
	 * @return string
	 */
	public static function removeVar(string $url, string $key): string
	{

		$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');

		$url = mb_substr($url, 0, -1);

		return ($url);
	}

	/**
	 * Build query string
	 *
	 * @param string $url
	 * @param array $array
	 * @return string
	 */
	public static function build(string $url, $array = []): string
	{

		foreach ($array as $k => $v) {
			if (is_array($v)) {
				$url = self::addVar($url, $k, serialize($v));
			} else {
				$url = self::addVar($url, $k, $v);
			}
		}

		return $url;
	}


	/**
	 * Clear query string
	 *
	 * @param string $url
	 * @return string
	 */
	public static function clear(string $url): string
	{
		return strtok($url, '?');
	}

	/**
	 * Encode a url on base64
	 *
	 * @param string $data
	 * @return string
	 */
	public static function safeB64Encode(string $data): string
	{
		$b64 = base64_encode($data);
		$b64 = str_replace(
			array('+', '/', '\r', '\n', '='),
			array('-', '_'),
			$b64
		);
		return $b64;
	}

	/**
	 * Decode a url from base64
	 *
	 * @param string $b64
	 * @return string
	 */
	public static function safeB64Decode(string $b64): string
	{
		$b64 = str_replace(
			array('-', '_'),
			array('+', '/'),
			$b64
		);
		return base64_decode($b64);
	}
}

?>