<?php

namespace App\Modules\Core\Events;

class EditorIsRendering
{
	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	private $value;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	private $attributes;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	private $content;

	/**
	 * Constructor
	 *
	 * @param  string $editor
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct($name, $value, $attributes = array())
	{
		$this->name = $name;
		$this->value = $value;
		$this->attributes = $attributes;
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Get the original, unaltered body
	 *
	 * @return mixed
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = (array)$attributes;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setContent($content)
	{
		$this->content = (string)$content;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function render()
	{
		return $this->content;
	}
}
