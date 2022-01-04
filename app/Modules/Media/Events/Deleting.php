<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Deleting
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * @var array
	 */
	private $items;

	/**
	 * Deleting constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = $request->input('disk', 'public');
		$this->items = $request->input('items');
	}

	/**
	 * @return string
	 */
	public function disk()
	{
		return $this->disk;
	}

	/**
	 * @return array
	 */
	public function items()
	{
		return $this->items;
	}
}
