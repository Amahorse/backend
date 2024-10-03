<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

class Date {

	public static function display(mixed $date, $time = false):string {

	if(!is_string($date)) {
		return '';
	}


	$date = date(config('locale','date_format'), strtotime($date));

	if($time) {
		$date .= ' ' . date(config('locale','time_format'), strtotime($date));
	}

	return $date;

	}

	/**
	 * Formatta data con nome giorno e mese
	 *
	 * @param string $date
	 * @return string
	 */
	public static function explain(string $date):string {

		if(function_exists('datefmt_create')) {

			$fmt = datefmt_create(
				config('locale','locale'), // The output language.
				\IntlDateFormatter::FULL,
				\IntlDateFormatter::FULL,
				pattern: "cccc d LLLL YYYY" // The output formatting.
			);
	
			$input = strtotime($date);
			$date = datefmt_format($fmt, $input);


		} else {

			//Questo in php 8.1 è deprecato
			@$date = ucfirst(strftime("%A %d %B", strtotime($date))) ;

		}

		return $date;
	}

	public static function monthsList() {
		$months = array();

		for ($i = 0; $i < 12; $i++) {
			$timestamp = mktime(0, 0, 0, date('n') - $i, 1);
			$months[date('m', $timestamp)] = ucfirst(strftime('%B', $timestamp));
		}

		ksort($months);
		return $months;
		}

	public static function addDays($days,$time = false) {

		if(!$time) {$time = time();}

		return date('Y-m-d h:i:s',strtotime('+'. (int)$days .' days',$time));
	}




	public static function timestampToSql($timestamp) {
		return date("Y-m-d H:m:s", $timestamp);
	}

	/**
	 * Aggiunge giorni lavorativi ad una data
	 *
	 * @param string 	$date
	 * @param mixed 	$days
	 * @param bool      $excludeToday	//Se questo parametro è true esclude data odierna dall'aggiungere i giorni quindi parte da -1
	 * @return int|null
	 */
	public static function addWorkingDays(string $date, mixed $days, $excludeToday = false):int|null
	{

		//Fix per tipo null o vuoto
		if(empty($days)) {
			$days = 0;
		} elseif($excludeToday == true) {
			$days = (int)$days -1;
		}

		$holidays = client('holidays');

		$year = date('Y', strtotime($date));

		$init = date('m-d', strtotime($date . ' +' . $days . ' weekdays'));
		/*
		while (in_array($init, $holidays)) {
			$init = date('m-d', strtotime($date . ' +' . ($days + 1) . ' weekdays'));
		}
		*/
		return strtotime($year . '-' . $init);
	}


}
?>