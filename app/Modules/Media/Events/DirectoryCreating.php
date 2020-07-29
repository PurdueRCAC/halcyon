<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class DirectoryCreating
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
		$this->disk = $request->input('disk', 'local');
		$this->path = $request->input('path');
		$this->name = $request->input('name');
	}

	/**
	 * @return string
	 */
	public function disk()
	{
		return $this->disk;
	}

	/**
	 * @return string
	 */
	public function path()
	{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function name()
	{
		return $this->name;
	}
}
