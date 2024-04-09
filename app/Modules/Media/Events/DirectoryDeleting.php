<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;
use App\Modules\Media\Contracts\DirectoryEvent;

class DirectoryDeleting implements DirectoryEvent
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
		$this->disk = urldecode((string)$request->input('disk', 'public'));
		$this->path = urldecode((string)$request->input('folder', ''));
		$this->name = basename($this->path);
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
