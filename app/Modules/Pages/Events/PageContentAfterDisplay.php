<?php

namespace App\Modules\Pages\Events;

class PageContentAfterDisplay
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
	public function __construct(string $body)
	{
		$this->body = $body;
		$this->original = $body;
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
	public function setBody(string $body): string
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
	public function __toString(): string
	{
		return $this->getBody();
	}
}
