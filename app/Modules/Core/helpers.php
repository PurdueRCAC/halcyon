<?php

if (!function_exists('editor'))
{
	/**
	 * Render an editor
	 * 
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array   $atts
	 * @param   string  $formatting
	 * @return  string
	 */
	function editor($name, $value, $atts = array(), $formatting = 'html')
	{
		$value = $value ?: '';
		event($event = new App\Modules\Core\Events\EditorIsRendering($name, $value, $atts, $formatting));

		return $event->render();
	}
}

if (!function_exists('markdown_editor'))
{
	/**
	 * Render a markdown editor
	 * 
	 * @param   string  $name
	 * @param   string  $value
	 * @param   array   $atts
	 * @return  string
	 */
	function markdown_editor($name, $value, $atts = array())
	{
		$value = $value ?: '';
		return editor($name, $value, $atts, 'markdown');
	}
}

if (!function_exists('captcha'))
{
	/**
	 * Render a CAPTCHA
	 * 
	 * @param   string  $name
	 * @param   array<string,mixed>   $atts
	 * @return  string
	 */
	function captcha($name, $atts = array())
	{
		event($event = new App\Modules\Core\Events\CaptchaIsRendering($name, $atts));

		return $event->render();
	}
}

if (!function_exists('validate_captcha'))
{
	/**
	 * Check if a CAPTCHA is valid
	 * 
	 * @param   string  $name
	 * @param   array   $atts
	 * @return  bool
	 */
	function validate_captcha($name, $atts = array())
	{
		event($event = new App\Modules\Core\Events\ValidateCaptcha($name, $atts));

		return $event->valid;
	}
}
