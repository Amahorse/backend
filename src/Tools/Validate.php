<?php

declare(strict_types=1);

namespace Kodelines\Tools;

use Kodelines\Db;
use Kodelines\Exception\ValidatorException;

class Validate
{


	/**
	 * Valida campo unico su tabella
	 *
	 * @param string $field
	 * @param int|string $value
	 * @param string $table
	 * @param boolean $id_exclude esclude un id, utile in caso di edit
	 * @return boolean
	 */
	public static function uniqField(string $field, int|string $value, string $table, int|bool $id_exclude = false): bool
	{

		if(is_string($value)) {
			$value = encode(mb_strtolower($value));
		}

		if (!$id_exclude) {

			if (Db::getRow('SELECT ' . $field . ' FROM ' . $table . ' WHERE LOWER(' . $field . ') = ' . $value)) {
				return false;
			}

			return true;
		}

		if (Db::getRow('SELECT ' . $field . ' FROM ' . $table . ' WHERE LOWER(' . $field . ') = ' . $value . ' AND id <> ' . $id_exclude)) {
			return false;
		}

		return true;
	}


	//control if string is iso code
	public static function isIsoCode(string $iso_code): bool
	{
		return (bool)preg_match('/^[a-zA-Z]{2,3}$/s', $iso_code);
	}

	//control if string is iso code
	public static function isZipCode(string $zip_code, mixed $regexp)
	{
	
		if (empty($regexp)) {
			return true;
		}

		return (bool)preg_match('/' . trim($regexp) . '/', $zip_code);
	}




	//control if string is valid phone number
	public static function isPhoneNumber(string $number): bool
	{
		return (bool)preg_match('/^[+0-9. ()-]*$/', $number);
	}


	//Check if Address is Valid
	public static function isEMail(string $email): bool
	{
		if (!filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			return true;
		}

		return false;
	}

	//check for alpha nueric value (no spaces)
	public static function isAlphanum(string $data): bool
	{
		return preg_match('/^[a-zA-Z0-9]+$/', $data);
	}

	public static function isUsername(string $data): bool
	{
		return preg_match('/^[a-zA-Z0-9_.-]{3,30}$/', $data);
	}



	public static function checkBoxValues($fields = array(), $post = array())
	{

		$return = array();

		foreach ($fields as $check) {
			if (isset($post[$check])) {
				$return[$check] = '1';
			} else {
				$return[$check] = '0';
			}
		}

		return $return;
	}



	/**
	 * VIES VAT number validation
	 *
	 * @param string $countryCode
	 * @param string $vatNumber
	 * @param int $timeout
	 */

	public static function viesCheckVAT($countryCode, $vatNumber, $timeout = 30)
	{

		$vies_url = 'http://ec.europa.eu/taxation_customs/vies/services/checkVatService';

		$response = array();
		$pattern = '/<(%s).*?>([\s\S]*)<\/\1/';
		$keys = array(
			'countryCode',
			'vatNumber',
			'requestDate',
			'valid',
			'name',
			'address'
		);

		$content = "<s11:Envelope xmlns:s11='http://schemas.xmlsoap.org/soap/envelope/'>
		  <s11:Body>
		    <tns1:checkVat xmlns:tns1='urn:ec.europa.eu:taxud:vies:services:checkVat:types'>
		      <tns1:countryCode>%s</tns1:countryCode>
		      <tns1:vatNumber>%s</tns1:vatNumber>
		    </tns1:checkVat>
		  </s11:Body>
		</s11:Envelope>";

		$opts = array(
			'http' => array(
				'method' => 'POST',
				'header' => "Content-Type: text/xml; charset=utf-8; SOAPAction: checkVatService",
				'content' => sprintf($content, $countryCode, $vatNumber),
				'timeout' => $timeout
			)
		);

		$ctx = stream_context_create($opts);
		$result = @file_get_contents($vies_url, false, $ctx);

		if (preg_match(sprintf($pattern, 'checkVatResponse'), $result, $matches)) {
			foreach ($keys as $key)
				preg_match(sprintf($pattern, $key), $matches[2], $value) && $response[$key] = $value[2];
		}
		return $response;
	}
}

?>