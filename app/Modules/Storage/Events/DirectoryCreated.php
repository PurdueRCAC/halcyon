<?php

namespace App\Modules\Storage\Events;

use App\Modules\Storage\Models\Directory;

class DirectoryCreated
{
	/**
	 * @var Directory
	 */
	public $directory;

	/**
	 * Constructor
	 *
	 * @param Directory $directory
	 * @return void
	 */
	public function __construct(Directory $directory)
	{
		$this->directory = $directory;
	}
}
