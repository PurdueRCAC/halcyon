<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;
use App\Modules\Media\Contracts\DirectoryEvent;

class DirectoryCreated implements DirectoryEvent
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * Constructor.
	 */
	public function __construct(string $disk, string $path = '', string $name = '')
	{
		$this->disk = $disk ? $disk : 'public';
		$this->path = $path;
		$this->name = $name;
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
	public function path(): string
	{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function name(): string
	{
		return $this->name;
	}
}
