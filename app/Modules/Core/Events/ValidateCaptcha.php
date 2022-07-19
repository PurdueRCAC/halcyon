<?php

namespace App\Modules\Core\Events;

class ValidateCaptcha
{
	/**
	 * Captcha name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Attributes for rendering
	 *
	 * @var string
	 */
	public $attributes;

	/**
	 * Validation state
	 *
	 * @var string
	 */
	public $valid = true;

	/**
	 * Constructor
	 *
	 * @param  string $name
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($name, $attributes = array())
	{
		$this->name = $name;
		$this->attributes = $attributes;
	}
}
