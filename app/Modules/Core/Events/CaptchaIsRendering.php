<?php

namespace App\Modules\Core\Events;

class CaptchaIsRendering
{
	/**
	 * The CAPTCHA name
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Any attributes to apply
	 *
	 * @var array<string,mixed>
	 */
	public $attributes;

	/**
	 * Is the captcha response valid
	 *
	 * @var bool
	 */
	public $valid = false;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Constructor
	 *
	 * @param  string $name
	 * @param  array<string,mixed>  $attributes
	 * @return void
	 */
	public function __construct($name, $attributes = array())
	{
		$this->name = $name;
		$this->attributes = $attributes;
	}

	/**
	 * Set the body
	 *
	 * @return string
	 */
	public function render()
	{
		return $this->content;
	}

	/**
	 * Set the content
	 *
	 * @param string $content
	 * @return void
	 */
	public function setContent(string $content): void
	{
		$this->content = $content;
	}
}
