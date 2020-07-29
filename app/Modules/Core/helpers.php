<?php

if (!function_exists('editor'))
{
	function editor($name, $value, $atts = array())
	{
		event($event = new App\Modules\Core\Events\EditorIsRendering($name, $value, $atts));

		return $event->render();
		//return view('core::components.textarea', compact('name', 'value', 'atts'));
	}
}
