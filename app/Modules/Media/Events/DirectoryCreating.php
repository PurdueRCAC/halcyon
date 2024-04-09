<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;
use App\Modules\Media\Contracts\DirectoryEvent;

class DirectoryCreating implements DirectoryEvent
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
	 * DirectoryCreating constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = (string)$request->input('disk', 'public');
		$this->path = (string)$request->input('path', '');
		$this->name = (string)$request->input('name', '');
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
