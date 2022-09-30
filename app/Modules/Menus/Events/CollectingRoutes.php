<?php

namespace App\Modules\Menus\Events;

class CollectingRoutes
{
	/**
	 * @var array
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
	 * @return \Illuminate\Database\Eloquent\Model
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
