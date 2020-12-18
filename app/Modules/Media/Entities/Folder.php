<?php

namespace App\Modules\Media\Entities;

/**
 * Media helper
 */
class Folder
{
	/**
	 * Find new sizes for an image
	 *
	 * @param   integer  $width   Original width
	 * @param   integer  $height  Original height
	 * @param   integer  $target  Target size
	 * @return  array
	 */
	public static function imageResize($width, $height, $target)
	{
		// Take the larger size of the width and height and applies the
		// formula accordingly...this is so this script will work
		// dynamically with any size image
		if ($width > $height)
		{
			$percentage = ($target / $width);
		}
		else
		{
			$percentage = ($target / $height);
		}

		// Get the new value, apply the percentage, and round the value
		$width  = round($width * $percentage);
		$height = round($height * $percentage);

		return array($width, $height);
	}

	/**
	 * Count files in a directory
	 *
	 * @param   string  $dir  Directory
	 * @return  array
	 */
	public static function countFiles($dir)
	{
		$total_file = 0;
		$total_dir = 0;

		if (is_dir($dir))
		{
			$d = dir($dir);

			while (false !== ($entry = $d->read()))
			{
				if (substr($entry, 0, 1) != '.'
				 && is_file($dir . DIRECTORY_SEPARATOR . $entry)
				 && strpos($entry, '.html') === false && strpos($entry, '.php') === false)
				{
					$total_file++;
				}

				if (substr($entry, 0, 1) != '.'
				 && is_dir($dir . DIRECTORY_SEPARATOR . $entry))
				{
					$total_dir++;
				}
			}

			$d->close();
		}

		return array($total_file, $total_dir);
	}

	/**
	 * Get parent directory
	 *
	 * @param   string  $folder
	 * @return  string
	 */
	public static function getParent($folder)
	{
		$parent = substr($folder, 0, strrpos($folder, '/'));
		return $parent;
	}

	/**
	 * Get children
	 *
	 * @param   string  $directory
	 * @param   string  $folder
	 * @return  array
	 */
	public static function getChildren($directory, $folder)
	{
		$files = array();
		foreach (app('files')->files($directory . $folder) as $child)
		{
			$file = array();
			$file['name'] = $child->getFilename();
			$file['path'] = $child->getPathname();
			$file['type'] = 'file';
			$file['ext']  = $child->getExtension();
			$file['size'] = $child->getSize();
			$file['rel']  = str_replace(storage_path() . '/app/public/', '', $file['path']);

			if (preg_match("/\.(bmp|gif|jpg|jpe|jpeg|png)$/i", $file['name']))
			{
				$file['type'] = 'img';
			}

			$files[] = $file;
		}

		return $files;
	}

	/**
	 * Build a folder tree
	 *
	 * @param   array   $folders
	 * @param   string  $path
	 * @return  void
	 */
	public static function _buildFolderTree($folders, $parent_id = 0, $path = '')
	{
		$branch = array();
		foreach ($folders as $folder)
		{
			if ($folder['parent'] == $parent_id)
			{
				$folder['path'] = ($path == '') ? $folder['name'] : $path . '/' . $folder['name'];

				$children = self::_buildFolderTree($folders, $folder['id'], $folder['path']);
				if ($children)
				{
					$folder['children'] = $children;
				}

				$branch[] = $folder;
			}
		}
		return $branch;
	}

	/**
	 * Build a path
	 *
	 * @param   array   $folders
	 * @param   string  $path
	 * @return  void
	 */
	public static function createPath(&$folders, $path)
	{
		foreach ($folders as &$folder)
		{
			$folder['path'] = str_replace($path, '', $folder['fullname']);
		}
	}


	private static $index = 0;

	/**
	 * Lists folder in format suitable for tree display.
	 *
	 * @param   string   $path      The path of the folder to read.
	 * @param   string   $filter    A filter for folder names.
	 * @param   integer  $maxLevel  The maximum number of levels to recursively read, defaults to three.
	 * @param   integer  $level     The current level, optional.
	 * @param   integer  $parent    Unique identifier of the parent folder, if any.
	 * @return  array
	 */
	public static function getTree($path, $filter = '.', $maxLevel = 3, $level = 0, $parent = 0)
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

				$fullName = $name; //$this->filesystem->cleanPath($path . DIRECTORY_SEPARATOR . $name['path']);
				$short = basename($fullName);

				$dirs[] = array(
					'id'       => self::$index,
					'parent'   => $parent,
					'name'     => ltrim($short, '\\/'),
					'fullname' => $fullName,
					'relname'  => str_replace(storage_path() . '/app', '', $fullName)
				);

				$dirs2 = self::getTree($fullName, $filter, $maxLevel, $level + 1, self::$index);

				$dirs = array_merge($dirs, $dirs2);
			}
		}

		return $dirs;
	}
}
