<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Updated
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * @var string
	 */
	private $before;

	/**
	 * @var string
	 */
	private $after;

	/**
	 * DirectoryCreating constructor.
	 *
	 * @param string $disk
	 * @param string $before
	 * @param string $after
	 */
	public function __construct($disk, $before, $after)
	{
		$this->disk = $disk;
		$this->before = $before;
		$this->after = $after;
	}

	/**
	 * @return string
	 */
	public function disk(): string
	{
		return $this->disk;
	}

	/**
	 * @return string
	 */
	public function before(): string
	{
		return $this->before;
	}

	/**
	 * @return string
	 */
	public function after(): string
	{
		return $this->after;
	}
}
