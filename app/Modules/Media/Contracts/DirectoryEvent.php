<?php

namespace App\Modules\Media\Contracts;

interface DirectoryEvent
{
	/**
	 * Get the storage disk
	 *
	 * @return string
	 */
	public function disk(): string;
}
