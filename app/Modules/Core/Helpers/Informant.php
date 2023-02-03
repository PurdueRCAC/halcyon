<?php
namespace App\Modules\Core\Helpers;

/**
 * Utility class working with phpsetting
 */
class Informant
{
	/**
	 * Method to generate a boolean message for a value
	 *
	 * @param   bool    $val  Is the value set?
	 * @return  string  html code
	 */
	public static function boolean($val): string
	{
		return '<span class="badge badge-' . ($val ? 'success">' . trans('global.on') : 'danger">' . trans('global.off')) . '</span>';
	}

	/**
	 * Method to generate a boolean message for a value
	 *
	 * @param   bool    $val Is the value set?
	 * @return  string  html code
	 */
	public static function set($val): string
	{
		return '<span class="badge badge-' . ($val ? 'success">' . trans('global.yes') : 'danger">' . trans('global.no')) . '</span>';
	}

	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  A php ini value
	 * @return  string  html code
	 */
	public static function string($val): string
	{
		return (empty($val) ? trans('global.none') : $val);
	}

	/**
	 * Method to generate an integer from a value
	 *
	 * @param   string   $val  A php ini value
	 * @return  int
	 */
	public static function integer($val): int
	{
		return intval($val);
	}

	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  A php ini value
	 * @return  string  html code
	 */
	public static function server($val): string
	{
		return (empty($val) ? trans('core::info.na') : $val);
	}

	/**
	 * Method to generate a (un)writable message for directory
	 *
	 * @param   bool    $writable  is the directory writable?
	 * @return  string  html code
	 */
	public static function writable($writable): string
	{
		if ($writable)
		{
			return '<span class="badge badge-success writable">' . trans('core::info.writable') . '</span>';
		}

		return '<span class="badge badge-danger unwritable">' . trans('core::info.unwritable') . '</span>';
	}

	/**
	 * Method to generate a message for a directory
	 *
	 * @param   string  $dir      the directory
	 * @param   string  $message  the message
	 * @param   bool    $visible  is the $dir visible?
	 * @return  string  html code
	 */
	public static function message(string $dir, string $message, bool $visible=true): string
	{
		$output ='';

		if ($visible)
		{
			$output = $dir;
		}

		return $output . ($message ? ' <strong>' . trans($message) . '</strong>' : '');
	}
}
