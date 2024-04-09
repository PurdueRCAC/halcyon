<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;
use App\Modules\Media\Contracts\DirectoryEvent;

class DirectoryUpdated implements DirectoryEvent
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
	public function __construct(string $disk, string $before, string $after)
	{
		$this->disk   = $disk ? $disk : 'public';
		$this->before = $before;
		$this->after  = $after;
	}

	/**
	 * @inheritdoc
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
