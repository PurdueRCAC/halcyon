<?php

namespace App\Modules\Core\Events;

class CaptchaIsRendering
{
	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	public $image;

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
	 * @param  string $editor
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($image = false, $attributes = array())
	{
		$this->image = $image;
		$this->attributes = $attributes;
	}

	/**
	 * Set the body
	 *
	 * @return void
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
	public function setContent($content)
	{
		$this->content = (string)$content;
	}
}
