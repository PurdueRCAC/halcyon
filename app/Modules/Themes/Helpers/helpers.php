<?php
if (!function_exists('theme_path'))
{
	/**
	 * Get the path to a theme
	 *
	 * @param string $filename
	 * @return string
	 */
	function theme_path($filename = null)
	{
		return app('themes')->getActiveThemePath($filename);
	}
}
