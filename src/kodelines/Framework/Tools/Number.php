<?php

declare(strict_types=1);

namespace Kodelines\Tools;

class Number {

	/**
	 * Check if number is between min and max
	 *
	 * @param integer|float $number
	 * @param integer|float $min
	 * @param integer|float $max
	 * @param boolean $equal 	if true min and max are included on check and returns true if equal
	 * @return boolean
	 */
	public static function isBetween(int|float $number, int|float $min, int|float $max, $equal = true): bool {

		if(empty($min) || $min == NULL) {
			$min = -INF;
		}

		if(empty($max) || $max == NULL) {
			$max = INF;
		}

		if($equal) {

			if($number >= $min && $number <= $max) {
				return true;
			}

			return false;
		}

		if($number > $min && $number < $max) {
			return true;
		}

	return false;

	}

	/**
	 * Get a number percentage
	 *
	 * @param integer|float $number
	 * @param integer|float $percentage
	 * @return integer|float
	 */
	public static function percentage(int|float $number, int|float $percentage): int|float {

		if(!is_numeric($number) || !is_numeric($percentage)) {
			return 0;
		}

		return self::format(($percentage / 100) * $number);

	}

	/**
	 * Add a Percentage to a number
	 *
	 * @param integer|float $number
	 * @param integer|float $percentage
	 * @return integer|float
	 */
	public static function addPercentage(int|float $number, int|float $percentage): int|float {

		if(!is_numeric($number) || !is_numeric($percentage)) {
			return 0;
		}

		return self::format($number * (1 + ($percentage / 100)));

	}

	/**
	 * Remove Percentage from a number
	 *
	 * @param integer|float $number
	 * @param integer|float $percentage
	 * @return integer|float
	 */
	public static function removePercentage(int|float $number, int|float $percentage): int|float {

		if(!is_numeric($number) || !is_numeric($percentage)) {
			return 0;
		}

		return self::format($number * (1 - ($percentage / 100)));

	}

	/**
	 * Get inverse percentage of a number
	 *
	 * @param integer|float $number
	 * @param integer|float $percentage
	 * @return integer|float
	 */
	public static function inversePercentage(int|float $number, int|float $percentage): int|float {

		return  self::format((100 - (($number / $percentage) * 100)));
	}

	/**
	 * Format a number on float with two decimal 
	 *
	 * @param integer|float $number
	 * @return float
	 */
	public static function format(int|float $number): float {
		return (float)number_format(round((float)$number,2 ,PHP_ROUND_HALF_UP), 2, '.', '');
	}

	/**
	 * Check if number is multiple of another
	 *
	 * @param integer|float $num
	 * @param integer|float $multiple
	 * @return boolean
	 */
	public static function isMultiple(int|float $num, int|float $multiple): bool {

		if ($num % $multiple == 0) {
			return true;
		}

		return false;
	}


}

?>