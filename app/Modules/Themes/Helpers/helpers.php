<?php
if (!function_exists('theme_path'))
{

	function theme_path($filename = null)
	{
		return app('themes')->themePath($filename);
	}
}
