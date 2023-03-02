<?php

namespace App\Modules\Orders\Helpers;

/**
 * Helper for dealing with currency
 */
class Currency
{
	/**
	 * Get the formatted number.
	 *
	 * @param   int  $value
	 * @return  string
	 */
	public static function formatNumber($value): string
	{
		$number = (int)preg_replace('/[^0-9\-]/', '', $value);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
	}
}
