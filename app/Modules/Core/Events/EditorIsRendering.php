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
	 * The body formatting
	 *
	 * @var string
	 */
	private $formatting;

	/**
	 * Constructor
	 *
	 * @param  string $name
	 * @param  string $value
	 * @param  array  $attributes
	 * @param  string $formatting
	 * @return void
	 */
	public function __construct($name, $value, $attributes = array(), $formatting = 'html')
	{
		$this->name = $name;
		$this->value = $value;
		$this->attributes = $attributes;
		$this->formatting = $formatting;
	}

	/**
	 * Get the name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Get the value
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Get the content format
	 *
	 * @return string
	 */
	public function getFormatting()
	{
		return $this->formatting;
	}

	/**
	 * Get the attributes
	 *
	 * @return array
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * Set the attributes
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function setAttributes($attributes)
	{
		$this->attributes = (array)$attributes;
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

	/**
	 * Render the content
	 *
	 * @return void
	 */
	public function render()
	{
		return $this->content;
	}
}
