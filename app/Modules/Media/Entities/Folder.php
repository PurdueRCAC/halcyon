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
	 * @return  bool
	 */
	public function isImage(): bool
	{
		return false;
	}

	/**
	 * Get image width (if an image)
	 *
	 * @return  int
	 */
	public function getWidth(): int
	{
		return 0;
	}

	/**
	 * Get image height (if an image)
	 *
	 * @return  int
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

	/**
	 * Get data as a tree
	 *
	 * @return array
	 */
	public function tree()
	{
		$folders = self::nest($this->getPathname());

		return self::buildTree($folders);
	}

	/**
	 * Build a folder tree
	 *
	 * @param   array   $folders
	 * @param   string  $path
	 * @return  array
	 */
	public static function buildTree($folders, $parent_id = 0, $path = '')
	{
		$branch = array();
		foreach ($folders as $folder)
		{
			if ($folder['parent'] == $parent_id)
			{
				$folder['path'] = ($path == '') ? $folder['name'] : $path . '/' . $folder['name'];

				$children = self::buildTree($folders, $folder['id'], $folder['path']);
				if ($children)
				{
					$folder['children'] = $children;
				}

				$branch[] = $folder;
			}
		}
		return $branch;
	}


	private static $index = 0;

	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   string   $filter    A filter for folder names.
	 * @param   int  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   int  $level     The current level, optional.
	 * @param   int  $parent    Unique identifier of the parent folder, if any.
	 * @return  array
	 */
	public static function nest($path = null, $filter = '.', $maxLevel = 10, $level = 0, $parent = 0)
	{
		$dirs = array();

		if ($level == 0)
		{
			self::$index = 0;
		}

		if ($level < $maxLevel)
		{
			$folders = app('files')->directories($path);

			// First path, index foldernames
			foreach ($folders as $name)
			{
				self::$index++;

				$fullName = $name;
				$short = basename($fullName);

				$dirs[] = array(
					'id'       => self::$index,
					'parent'   => $parent,
					'name'     => ltrim($short, '\\/'),
					'fullname' => $fullName,
					'relname'  => str_replace(storage_path('app/public'), '', $fullName)
				);

				$dirs2 = self::nest($fullName, $filter, $maxLevel, $level + 1, self::$index);

				$dirs = array_merge($dirs, $dirs2);
			}
		}

		return $dirs;
	}
}
