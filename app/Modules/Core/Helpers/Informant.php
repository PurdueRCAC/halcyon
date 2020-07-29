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
	 * @param   boolean  $val  Is the value set?
	 * @return  string   html code
	 */
	public static function boolean($val)
	{
		return ($val ? '<span class="state on"><span>' . trans('global.on') : '<span class="state off"><span>' . trans('global.off')) . '</span></span>';
	}

	/**
	 * Method to generate a boolean message for a value
	 *
	 * @param   boolean  $val Is the value set?
	 * @return  string   html code
	 */
	public static function set($val)
	{
		return ($val ? '<span class="state yes"><span>' . trans('global.yes') : '<span class="state no"><span>' . trans('global.no')) . '</span></span>';
	}

	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  A php ini value
	 * @return  string  html code
	 */
	public static function string($val)
	{
		return (empty($val) ? trans('global.none') : $val);
	}

	/**
	 * Method to generate an integer from a value
	 *
	 * @param   string   $val  A php ini value
	 * @return  integer
	 */
	public static function integer($val)
	{
		return intval($val);
	}

	/**
	 * Method to generate a string message for a value
	 *
	 * @param   string  $val  A php ini value
	 * @return  string  html code
	 */
	public static function server($val)
	{
		return (empty($val) ? trans('core::info.na') : $val);
	}

	/**
	 * Method to generate a (un)writable message for directory
	 *
	 * @param   boolean  $writable  is the directory writable?
	 * @return  string   html code
	 */
	public static function writable($writable)
	{
		if ($writable)
		{
			return '<span class="writable">' . trans('core::info.WRITABLE') . '</span>';
		}
		else
		{
			return '<span class="unwritable">' . trans('core::info.UNWRITABLE') . '</span>';
		}
	}

	/**
	 * Method to generate a message for a directory
	 *
	 * @param   string   $dir      the directory
	 * @param   boolean  $message  the message
	 * @param   boolean  $visible  is the $dir visible?
	 * @return  string   html code
	 */
	public static function message($dir, $message, $visible=true)
	{
		if ($visible)
		{
			$output = $dir;
		}
		else
		{
			$output ='';
		}

		if (empty($message))
		{
			return $output;
		}

		return $output . ' <strong>' . trans($message) . '</strong>';
	}
}
