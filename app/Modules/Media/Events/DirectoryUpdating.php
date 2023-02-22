<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class DirectoryUpdating
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
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk   = (string)$request->input('disk', 'public');
		$this->before = (string)$request->input('before', '');
		$this->after  = (string)$request->input('after', '');
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
