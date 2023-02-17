<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Rename
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * @var string
	 */
	private $newName;

	/**
	 * @var string
	 */
	private $oldName;

	/**
	 * Rename constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = $request->input('disk', 'public');
		$this->newName = $request->input('newName', '');
		$this->oldName = $request->input('oldName', '');
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
	public function newName(): string
	{
		return $this->newName;
	}

	/**
	 * @return string
	 */
	public function oldName(): string
	{
		return $this->oldName;
	}
}
