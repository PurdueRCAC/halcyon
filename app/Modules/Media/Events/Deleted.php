<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class Deleted
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
	 * Deleted constructor.
	 *
	 * @param string $disk
	 * @param array $items
	 */
	public function __construct($disk, $items)
	{
		$this->disk = $disk;
		$this->items = $items;
	}

	/**
	 * @return string
	 */
	public function disk(): string
	{
		return $this->disk;
	}

	/**
	 * @return array
	 */
	public function items(): array
	{
		return $this->items;
	}
}
