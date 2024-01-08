<?php

namespace App\Halcyon\Html\Builder;

use Carbon\Carbon;

/**
 * Utility class for form elements
 */
class Input
{
	/**
	 * Create a form input field.
	 *
	 * @param   string  $type
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function input($type, $name, $value = null, $options = array())
	{
		//if (!isset($options['name'])) $options['name'] = $name;

		// We will get the appropriate value for the given field. We will look for the
		// value in the session for the value in the old input data then we'll look
		// in the model instance if one is set. Otherwise we will just use empty.
		$id = self::getIdAttribute($name, $options);

		// Once we have the type, value, and ID we can merge them into the rest of the
		// attributes array so we can convert them into their HTML attribute format
		// when creating the HTML element. Then, we will return the entire input.
		$merge = compact('type', 'name', 'value', 'id');

		$options = array_merge($options, $merge);

		if ($type != 'hidden')
		{
			if (!isset($options['class']))
			{
				$options['class'] = 'form-control';
			}
			else
			{
				$options['class'] .= ' form-control';
			}
			$options['class'] = trim($options['class']);
		}

		return '<input' . self::attributes($options) . ' />';
	}

	/**
	 * Create a text input field.
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function text($name, $value = null, $options = array())
	{
		return self::input('text', $name, $value, $options);
	}

	/**
	 * Create a password input field.
	 *
	 * @param   string  $name
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function password($name, $options = array())
	{
		return self::input('password', $name, '', $options);
	}

	/**
	 * Create a hidden input field.
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function hidden($name, $value = null, $options = array())
	{
		return self::input('hidden', $name, $value, $options);
	}

	/**
	 * Create an e-mail input field.
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function email($name, $value = null, $options = array())
	{
		return self::input('email', $name, $value, $options);
	}

	/**
	 * Create a url input field.
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function url($name, $value = null, $options = array())
	{
		return self::input('url', $name, $value, $options);
	}

	/**
	 * Create a file input field.
	 *
	 * @param   string  $name
	 * @param   array<string,string>   $options
	 * @return  string
	 */
	public static function file($name, $options = array())
	{
		return self::input('file', $name, null, $options);
	}

	/**
	 * Displays a calendar control field
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string  HTML markup for a calendar field
	 */
	public static function calendar($name, $value = null, $options = array())
	{
		static $done;

		if ($done === null)
		{
			$done = array();
		}

		$readonly = isset($options['readonly']) && $options['readonly'] == 'readonly';
		$disabled = isset($options['disabled']) && $options['disabled'] == 'disabled';
		$time     = true;
		if (isset($options['time']))
		{
			$time = (bool)$options['time'];
			unset($options['time']);
		}

		$format = 'yy-mm-dd';
		if (isset($options['format']))
		{
			$format = $options['format'] ? $options['format'] : $format;
			unset($options['format']);
		}

		if (!isset($options['class']))
		{
			$options['class'] = 'calendar-field';
		}
		else
		{
			$options['class'] .= ' calendar-field';
		}

		if (!$readonly && !$disabled)
		{
			$id = self::getIdAttribute($name, $options);

			// Only display the triggers once for each control.
			if (!in_array($id, $done))
			{
				if ($format == 'Y-m-d H:i:s' || $format == '%Y-%m-%d %H:%M:%S')
				{
					$time = true;
				}
				$altformats = array('Y-m-d H:i:s', '%Y-%m-%d %H:%M:%S', 'Y-m-d', '%Y-%m-%d');

				$options['class'] .= ' date' . ($time ? 'time' : '');

				$format = (in_array($format, $altformats) ? 'yy-mm-dd' : $format);

				$done[] = $id;
			}

			return '<span class="input-group input-datetime">' . self::text($name, $value, $options) . '<span class="input-group-append"><span class="input-group-text fa fa-calendar"></span></span></span>';
		}
		else
		{
			$value = (0 !== (int) $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : '');

			return self::text($name . 'disabled', $value, $options) .
				   self::hidden($name, $value, $options);
		}
	}

	/**
	 * Displays a color picker control field
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array<string,string>   $options
	 * @return  string  HTML markup for a calendar field
	 */
	public static function colorpicker($name, $value = null, $options = array())
	{
		static $done;

		if ($done === null)
		{
			$done = array();
		}

		$readonly = isset($options['readonly']) && $options['readonly'] == 'readonly';
		$disabled = isset($options['disabled']) && $options['disabled'] == 'disabled';

		$options['class'] = 'input-colorpicker';

		$value = $value ? '#' . ltrim($value, '#') : '';

		if (!$readonly && !$disabled)
		{
			$id = self::getIdAttribute($name, $options);

			// Only display the triggers once for each control.
			if (!in_array($id, $done))
			{
				$done[] = $id;
			}

			return '<span class="input-color">' . self::text($name, $value, $options) . '</span>';
		}

		return self::text($name . 'disabled', $value, $options) . self::hidden($name, $value, $options);
	}

	/**
	 * Get the ID attribute for a field name.
	 *
	 * @param   string  $name
	 * @param   array<string,string>   $attributes
	 * @return  string
	 */
	protected static function getIdAttribute($name, $attributes)
	{
		if (array_key_exists('id', $attributes))
		{
			return $attributes['id'];
		}

		return self::transformKey($name);
	}

	/**
	 * Transform key from array to dot syntax.
	 *
	 * @param   string  $key
	 * @return  string
	 */
	protected static function transformKey($key)
	{
		return str_replace(array('.', '[]', '[', ']'), array('_', '', '-', ''), $key);
	}

	/**
	 * Build an HTML attribute string from an array.
	 *
	 * @param   array<string,mixed>  $attributes
	 * @return  string
	 */
	protected static function attributes($attributes)
	{
		$html = array();

		// For numeric keys we will assume that the key and the value are the same
		// as this will convert HTML attributes such as "required" to a correct
		// form like required="required" instead of using incorrect numerics.
		foreach ((array) $attributes as $key => $value)
		{
			$element = null;

			if (is_numeric($key))
			{
				$key = $value;
			}

			if (!is_null($value))
			{
				$element = $key . '="' . htmlentities($value, ENT_COMPAT, 'UTF-8') . '"';
			}

			if (!is_null($element))
			{
				$html[] = $element;
			}
		}

		return count($html) > 0 ? ' ' . implode(' ', $html) : '';
	}
}
