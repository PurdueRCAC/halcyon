<?php

namespace App\Modules\News\Events;

class ArticlePrepareContent
{
	/**
	 * The rendered body of the article
	 *
	 * @var string
	 */
	private $body;

	/**
	 * The original body of the article to render
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
		$this->body = $body;
		$this->original = $body;
	}

	/**
	 * Get the article body
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Get the original, unaltered body
	 *
	 * @return mixed
	 */
	public function getOriginal()
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
