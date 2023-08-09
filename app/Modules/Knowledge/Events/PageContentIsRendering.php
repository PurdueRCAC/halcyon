<?php

namespace App\Modules\Knowledge\Events;

class PageContentIsRendering
{
	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	private $body;

	/**
	 * The original body of the page to render
	 *
	 * @var string
	 */
	private $original;

	/**
	 * Constructor
	 *
	 * @param  string $body
	 * @return void
	 */
	public function __construct($body)
	{
		$this->body = (string) $body;
		$this->original = $this->body;
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setBody($body): void
	{
		$this->body = $body;
	}

	/**
	 * Get the original, unaltered body
	 *
	 * @return string
	 */
	public function getOriginal(): string
	{
		return $this->original;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getBody();
	}
}
