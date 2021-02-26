<?php

namespace App\Modules\Core\Events;

class ValidateCaptcha
{
	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	public $attributes;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	public $valid = true;

	/**
	 * Constructor
	 *
	 * @param  string $editor
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($name, $attributes = array())
	{
		$this->name = $name;
		$this->attributes = $attributes;
	}
}
