<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;
use App\Modules\Media\Contracts\DirectoryEvent;

class DirectoryDeleted implements DirectoryEvent
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
	 * @param array  $items
	 */
	public function __construct(string $disk, array $items)
	{
		$this->disk  = $disk ? $disk : 'public';
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
