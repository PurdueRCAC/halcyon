<?php

namespace App\Modules\Media\Entities;

/**
 * Folder
 */
class Folder extends File
{
	/**
	 * Checks if the file is an image
	 *
	 * @return  boolean
	 */
	public function isImage(): bool
	{
		return false;
	}

	/**
	 * Get image width (if an image)
	 *
	 * @return  integer
	 */
	public function getWidth(): int
	{
		return 0;
	}

	/**
	 * Get image height (if an image)
	 *
	 * @return  integer
	 */
	public function getHeight(): int
	{
		return 0;
	}

	/**
	 * Get children
	 *
	 * @return  array
	 */
	public function children()
	{
		$path = $this->getPathname();

		$files = array();

		foreach (app('files')->files($path) as $child)
		{
			$files[] = new File($child->getPathname());
		}

		foreach (app('files')->directories($path) as $child)
		{
			$files[] = new self($child);
		}

		return collect($files);
	}
}
