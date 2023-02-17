<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class DirectoryDeleting
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
		$this->disk = urldecode($request->input('disk', 'public'));
		$this->path = urldecode($request->input('folder', ''));
		$this->name = basename($this->path);
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
