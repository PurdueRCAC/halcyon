<?php

namespace App\Modules\Menus\Events;

class CollectingRoutes
{
	/**
	 * @var array<string,array>
	 */
	public $routes;

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->routes = array();
	}

	/**
	 * Return the entity
	 *
	 * @param  string  $group
	 * @param  string  $text
	 * @param  string  $value
	 * @param  string  $path
	 * @param  string  $indent
	 * @return CollectingRoutes
	 */
	public function addRoute($group, $text, $value, $path = '', $indent = '')
	{
		if (!isset($this->routes[$group]))
		{
			$this->routes[$group] = array();
		}

		$this->routes[$group][] = array(
			'text'   => $text,
			'value'  => $value,
			'path'   => $path,
			'indent' => $indent,
		);

		return $this;
	}
}
