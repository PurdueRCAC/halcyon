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
	private $imageExtensions = ['jpg', 'png', 'jpeg', 'gif', 'svg', 'bmp', 'jpe'];

	private $dimensions = null;

	/**
	 * Checks if the file is an image
	 *
	 * @return  boolean
	 */
	public function isImage(): bool
	{
		return in_array($this->getExtension(), $this->imageExtensions);
	}

	public function getRelativePath(): string
	{
		return str_replace(storage_path() . '/app/', '', $this->getPathname());
	}

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

	public function getId(): string
	{
		// Get a shortened name
		$path = str_replace('/', '_', $this->getPathname());
		$path = preg_replace('/[^a-zA-Z0-9\-_]+/', '', $path);

		return $path;
	}

	/**
	 * Return file size
	 *
	 * @return  string
	 */
	public function getFormattedSize(): string
	{
		return MediaHelper::parseSize($this->getSize());
	}

	public function getLastModified()
	{
		return new Carbon($this->getMTime());
	}

	public function getWidth()
	{
		return $this->calculateDimension('width');
	}

	public function getHeight()
	{
		return $this->calculateDimension('height');
	}

	private function calculateDimension($dim)
	{
		if (empty($this->dimensions))
		{
			$this->dimensions = array(
				'width' => 0,
				'height' => 0
			);

			if ($this->isImage())
			{
				try
				{
					$dimensions = getimagesize($this->getPathname());

					$this->dimensions['width'] = $dimensions[0];
					$this->dimensions['height'] = $dimensions[1];
				}
				catch (\Exception $e)
				{
				}
			}
		}

		return $this->dimensions['width'];
	}

	public function getUrl()
	{
		$path = '/' . $this->getRelativePath();
		if (Str::contains($path, '/public/'))
		{
			$path = Str::replaceFirst('/public/', '/', $path);
		}
		return config('filesystems.disks.public.url') . $path;
		//return url('/') . Storage::url($this->getRelativePath());
	}

	public function getPublicPath()
	{
		$base = config('filesystems.disks.public.url'); //url('/') . '/storage';
		return str_replace($base, '', $this->getUrl());
	}
}
