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
	 * @var array
	 */
	public $attributes;

	/**
	 * Validation state
	 *
	 * @var bool
	 */
	public $valid = true;

	/**
	 * Constructor
	 *
	 * @param  string $name
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct(string $name, array $attributes = array())
	{
		$this->name = $name;
		$this->attributes = $attributes;
	}
}
