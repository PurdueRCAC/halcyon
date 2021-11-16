<?php

namespace App\Modules\Media\Entities;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Modules\Media\Helpers\MediaHelper;
use Carbon\Carbon;

/**
 * File
 */
class File extends \SplFileInfo
{
	/**
	 * Image file types
	 *
	 * @var  array
	 */
	private $imageExtensions = ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'svg', 'bmp'];

	/**
	 * Image pixel dimensions (width and height)
	 *
	 * @var  array
	 */
	private $dimensions = null;

	/**
	 * Checks if the file is an image
	 *
	 * @return  boolean
	 */
	public function isImage(): bool
	{
		return in_array(strtolower($this->getExtension()), $this->imageExtensions);
	}

	/**
	 * Get file path, relative to public storage directory
	 *
	 * @return  string
	 */
	public function getRelativePath(): string
	{
		return str_replace(storage_path('app/public'), '', $this->getPathname());
	}

	/**
	 * Get a shortened file name
	 * 
	 * ex: "really_long_name ... .png"
	 *
	 * @return  string
	 */
	public function getShortName(): string
	{
		// Get a shortened name
		$name = preg_replace('#\.[^.]*$#', '', $this->getFilename());

		if (strlen($name) > 15)
		{
			$name = substr($name, 0, 10) . ' ... ';
		}

		if ($this->isFile())
		{
			$name .= '.' . $this->getExtension();
		}

		return $name;
	}

	/**
	 * Generate an ID based on the full file path
	 *
	 * @return  string
	 */
	public function getId(): string
	{
		// Get a shortened name
		$path = str_replace('/', '_', $this->getPathname());
		$path = preg_replace('/[^a-zA-Z0-9\-_]+/', '', $path);

		return $path;
	}

	/**
	 * Return human-readable file size
	 *
	 * @return  string
	 */
	public function getFormattedSize(): string
	{
		$size = $this->getSize();

		if ($size < 1024)
		{
			return trans('media::media.filesize bytes', ['size' => $size]);
		}
		elseif ($size < 1024 * 1024)
		{
			return trans('media::media.filesize kilobytes', ['size' => sprintf('%01.2f', $size / 1024.0)]);
		}

		return trans('media::media.filesize megabytes', ['size' => sprintf('%01.2f', $size / (1024.0 * 1024))]);
	}

	/**
	 * Get last modified time
	 *
	 * @return  object  Carbon
	 */
	public function getLastModified()
	{
		return new Carbon($this->getMTime());
	}

	/**
	 * Get image width (if an image)
	 *
	 * @return  integer
	 */
	public function getWidth(): int
	{
		return $this->calculateDimension('width');
	}

	/**
	 * Get image height (if an image)
	 *
	 * @return  integer
	 */
	public function getHeight(): int
	{
		return $this->calculateDimension('height');
	}

	/**
	 * Get image pixel dimension (width or height)
	 *
	 * @param   string  $dim
	 * @return  integer
	 */
	private function calculateDimension($dim): int
	{
		if (empty($this->dimensions))
		{
			$this->dimensions = array(
				'width'  => 0,
				'height' => 0
			);

			if ($this->isImage() && $this->getExtension() != 'svg')
			{
				try
				{
					$dimensions = getimagesize($this->getPathname());

					$this->dimensions['width']  = $dimensions[0];
					$this->dimensions['height'] = $dimensions[1];
				}
				catch (\Exception $e)
				{
				}
			}
		}

		return $this->dimensions[$dim];
	}

	/**
	 * Get file URL
	 *
	 * @return  string
	 */
	public function getUrl(): string
	{
		$path = '/' . trim($this->getRelativePath(), '/');
		if (Str::contains($path, '/public/'))
		{
			$path = Str::replaceFirst('/public/', '/', $path);
		}
		$path = preg_replace('/\/{2,}/', '/', $path);
		return rtrim(config('filesystems.disks.public.url'), '/') . $path;
	}

	/**
	 * Get public file path (URL without host and base path)
	 *
	 * @return  string
	 */
	public function getPublicPath(): string
	{
		$base = config('filesystems.disks.public.url');
		$path = str_replace($base, '', $this->getUrl());
		$path = preg_replace('/\/{2,}/', '/', $path);
		return $path;
	}
}
