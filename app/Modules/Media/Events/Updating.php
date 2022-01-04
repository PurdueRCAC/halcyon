<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Updating
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
	 * Constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = $request->input('disk', 'public');
		$this->before = $request->input('before');
		$this->after = $request->input('after');
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
	public function before()
	{
		return $this->before;
	}

	/**
	 * @return string
	 */
	public function after()
	{
		return $this->after;
	}
}
