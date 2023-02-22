<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Paste
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
	 * @var array
	 */
	private $clipboard;

	/**
	 * Paste constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = (string)$request->input('disk', 'public');
		$this->path = (string)$request->input('path', '');
		$this->clipboard = $request->input('clipboard', []);
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
	 * @return array
	 */
	public function clipboard(): array
	{
		return $this->clipboard;
	}
}
