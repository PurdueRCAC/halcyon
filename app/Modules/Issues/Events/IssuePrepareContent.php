<?php

namespace App\Modules\Issues\Events;

class IssuePrepareContent
{
	/**
	 * The rendered body of the report
	 *
	 * @var string
	 */
	private $body;

	/**
	 * The original body of the report to render
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
	 * Get the report body
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
