<?php

namespace App\Modules\Media\Events;


class DiskSelected
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * DiskSelected constructor.
	 *
	 * @param string $disk
	 */
	public function __construct(string $disk)
	{
		$this->disk = $disk;
	}

	/**
	 * @return string
	 */
	public function disk(): string
	{
		return $this->disk;
	}
}
