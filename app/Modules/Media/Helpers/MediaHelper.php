<?php

namespace App\Modules\Media\Helpers;

use App\Modules\Media\Entities\File;

/**
 * Media helper
 */
class MediaHelper
{
	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   array    $file  File information
	 * @param   string   $err   An error message to be returned
	 * @return  boolean
	 */
	public static function canUpload($file, &$err)
	{
		$params = config('module.media');

		if (empty($file['name']))
		{
			$err = 'media::media.error.UPLOAD_INPUT';
			return false;
		}

		if ($file['name'] !== Filesystem::clean($file['name']))
		{
			$err = 'media::media.error.WARNFILENAME';
			return false;
		}

		$format = strtolower(Filesystem::extension($file['name']));

		// Media file names should never have executable extensions buried in them.
		$executable = array(
			'php', 'js', 'exe', 'phtml', 'java', 'perl', 'py', 'asp','dll', 'go', 'ade', 'adp', 'bat', 'chm', 'cmd', 'com', 'cpl', 'hta', 'ins', 'isp',
			'jse', 'lib', 'mde', 'msc', 'msp', 'mst', 'pif', 'scr', 'sct', 'shb', 'sys', 'vb', 'vbe', 'vbs', 'vxd', 'wsc', 'wsf', 'wsh'
		);

		$explodedFileName = explode('.', $file['name']);

		if (count($explodedFileName) > 2)
		{
			foreach ($executable as $extensionName)
			{
				if (in_array($extensionName, $explodedFileName))
				{
					$err = 'media::media.error.WARNFILETYPE';
					return false;
				}
			}
		}

		$allowable = explode(',', $params->get('upload_extensions'));
		$ignored   = explode(',', $params->get('ignore_extensions'));

		if ($format == '' || $format == false || (!in_array($format, $allowable) && !in_array($format, $ignored)))
		{
			$err = 'media::media.error.WARNFILETYPE';
			return false;
		}

		$maxSize = (int) ($params->get('upload_maxsize', 0) * 1024 * 1024);
		if ($maxSize > 0 && (int) $file['size'] > $maxSize)
		{
			$err = 'media::media.error.WARNFILETOOLARGE';
			return false;
		}

		$imginfo = null;
		if ($params->get('restrict_uploads', 1))
		{
			$images = explode(',', $params->get('image_extensions'));

			// if it's an image run it through getimagesize
			if (in_array($format, $images))
			{
				// if tmp_name is empty, then the file was bigger than the PHP limit
				if (!empty($file['tmp_name']))
				{
					if (($imginfo = getimagesize($file['tmp_name'])) === false)
					{
						$err = 'media::media.error.WARNINVALID_IMG';
						return false;
					}
				}
				else
				{
					$err = 'media::media.error.WARNFILETOOLARGE';
					return false;
				}
			}
			elseif (!in_array($format, $ignored))
			{
				// if its not an image...and we're not ignoring it
				$allowed_mime = explode(',', $params->get('upload_mime'));
				$illegal_mime = explode(',', $params->get('upload_mime_illegal'));

				if (function_exists('finfo_open') && $params->get('check_mime', 1))
				{
					// We have fileinfo
					$finfo = finfo_open(FILEINFO_MIME);
					$type = finfo_file($finfo, $file['tmp_name']);
					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$err = 'media::media.error.WARNINVALID_MIME';
						return false;
					}

					finfo_close($finfo);
				}
				elseif (function_exists('mime_content_type') && $params->get('check_mime', 1))
				{
					// we have mime magic
					$type = mime_content_type($file['tmp_name']);

					if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime))
					{
						$err = 'media::media.error.WARNINVALID_MIME';
						return false;
					}
				}
				elseif (!auth()->user() || !auth()->user()->can('manage'))
				{
					$err = 'media::media.error.WARNNOTADMIN';
					return false;
				}
			}
		}

		if (!auth()->user() || !auth()->user()->can('admin'))
		{
			$xss_check = Filesystem::read($file['tmp_name'], false, 256);

			$html_tags = array(
				'abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound',
				'big', 'blackface', 'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite',
				'code', 'col', 'colgroup', 'comment', 'custom', 'dd', 'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em',
				'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
				'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label',
				'layer', 'legend', 'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol',
				'nobr', 'noembed', 'noframes', 'noscript', 'nosmartquotes', 'object', 'ol', 'optgroup', 'option',
				'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select', 'server', 'shadow',
				'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td',
				'textarea', 'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--'
			);

			foreach ($html_tags as $tag)
			{
				// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
				if (stristr($xss_check, '<' . $tag . ' ')
				 || stristr($xss_check, '<' . $tag . '>'))
				{
					$err = 'media::media.error.WARNIEXSS';
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Sanitize file names
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public static function sanitize($name)
	{
		if (!preg_match('/^[\x20-\x7e]*$/', $name))
		{
			$name = \Illuminate\Support\Facades\Str::ascii($name);
		}
		$name = preg_replace(
			'~
			[<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
			[\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
			[\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
			[#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
			[{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
			~x',
			'-', $name
		);
		// avoids ".", ".." or ".hiddenFiles"
		$name = ltrim($name, '.-');

		// reduce consecutive characters
		$name = preg_replace(array(
			// "file   name.zip" becomes "file-name.zip"
			'/ +/',
			// "file___name.zip" becomes "file-name.zip"
			'/_+/',
			// "file---name.zip" becomes "file-name.zip"
			'/-+/'
		), '-', $name);
		$name = preg_replace(array(
			// "file--.--.-.--name.zip" becomes "file.name.zip"
			'/-*\.-*/',
			// "file...name..zip" becomes "file.name.zip"
			'/\.{2,}/'
		), '.', $name);
		// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
		$name = mb_strtolower($name, mb_detect_encoding($name));
		// ".file-name.-" becomes "file-name"
		$name = trim($name, '.-');

		return $name;
	}

	/**
	 * Returns a quantifier based on the argument
	 *
	 * @param   integer  $size  Numeric size of a file
	 * @return  string
	 */
	public static function parseSize($size)
	{
		if ($size < 1024)
		{
			return trans('media::media.filesize bytes', ['size' => $size]);
		}
		elseif ($size < 1024 * 1024)
		{
			return trans('media::media.filesize kilobytes', ['size' => sprintf('%01.2f', $size / 1024.0)]);
		}
		else
		{
			return trans('media::media.filesize megabytes', ['size' => sprintf('%01.2f', $size / (1024.0 * 1024))]);
		}
	}

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
			/*$file = array();
			$file['name'] = $child->getFilename();
			$file['path'] = $child->getPathname();
			$file['type'] = 'file';
			$file['ext']  = $child->getExtension();
			$file['size'] = $child->getSize();
			$file['rel']  = str_replace(storage_path() . '/app/public/', '', $file['path']);

			if (preg_match("/\.(bmp|gif|jpg|jpe|jpeg|png)$/i", $file['name']))
			{
				$file['type'] = 'img';
			}*/

			$files[] = new File($child->getPathname()); //$file;
		}

		foreach (app('files')->directories($directory . $folder) as $child)
		{
			/*$file = array();
			$file['name'] = basename($child);
			$file['path'] = $child;
			$file['type'] = 'dir';
			$file['ext']  = ''; //$child->getExtension();
			$file['size'] = 0; //$child->getSize();
			$file['rel']  = trim(str_replace(storage_path() . '/app', '', $file['path']), '/');*/

			$files[] = new File($child); //$file;
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
	public static function getTree($path, $filter = '.', $maxLevel = 10, $level = 0, $parent = 0)
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
					'relname'  => str_replace(storage_path() . '/app', '', $fullName)
				);

				$dirs2 = self::getTree($fullName, $filter, $maxLevel, $level + 1, self::$index);

				$dirs = array_merge($dirs, $dirs2);
			}
		}

		return $dirs;
	}
}
