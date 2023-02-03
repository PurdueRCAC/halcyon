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
	 * @return  Response
	 */
	function editor($name, $value, $atts = array(), $formatting = 'html')
	{
		event($event = new App\Modules\Core\Events\EditorIsRendering($name, $value, $atts, $formatting));

		return $event->render();
		//return view('core::components.textarea', compact('name', 'value', 'atts'));
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
	 * @return  Response
	 */
	function markdown_editor($name, $value, $atts = array())
	{
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
	 * @return  Response
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
	 * @return  Response
	 */
	function validate_captcha($name, $atts = array())
	{
		event($event = new App\Modules\Core\Events\ValidateCaptcha($name, $atts));

		return $event->valid;
	}
}
