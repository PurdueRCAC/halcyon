<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Download
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
	 * Download constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = (string)$request->input('disk', 'public');
		$this->path = (string)$request->input('path', '');
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
}
