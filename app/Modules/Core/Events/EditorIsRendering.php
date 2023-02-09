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
	public function __construct(string $name, string $value, array $attributes = array(), string $formatting = 'html')
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
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Set the name
	 *
	 * @param string $name
	 * @return void
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * Get the value
	 *
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * Get the content format
	 *
	 * @return string
	 */
	public function getFormatting(): string
	{
		return $this->formatting;
	}

	/**
	 * Get the attributes
	 *
	 * @return array
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Set the attributes
	 *
	 * @param  array $attributes
	 * @return void
	 */
	public function setAttributes(array $attributes): void
	{
		$this->attributes = $attributes;
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

	/**
	 * Render the content
	 *
	 * @return string
	 */
	public function render(): string
	{
		if (!$this->content)
		{
			$attr = $this->getAttributes();
			$attr['name'] = $this->getName();

			if (!isset($attr['cols']))
			{
				$attr['cols'] = 35;
			}

			if (!isset($attr['rows']))
			{
				$attr['rows'] = 5;
			}

			if (!isset($attr['id']))
			{
				$attr['id'] = str_replace(['[', ']'], ['-', ''], $attr['name']);
			}

			if (!isset($attr['class']))
			{
				$attr['class'] = '';
			}
			$attr['class'] .= ' form-control';
			$attr['class'] = trim($attr['class']);

			$attributes = '';
			foreach ($attr as $k => $v)
			{
				$attributes .= ' ' . $k . '="' . e($v) . '"';
			}

			$this->content = '<textarea ' . $attributes . '>' . $this->content . '</textarea>';
		}

		return $this->content;
	}
}
