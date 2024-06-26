<?php

namespace App\Modules\Media\Helpers;

use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * Helper class for image manipulation
 */
class ImageProcessor
{
	/**
	 * Path to image
	 *
	 * @var  string
	 */
	private $source = null;

	/**
	 * Manipulated image data
	 *
	 * @var  string
	 */
	private $resource = null;

	/**
	 * Image type (png, gif, jpg)
	 *
	 * @var  string
	 */
	private $image_type = IMAGETYPE_PNG;

	/**
	 * EXIF image data
	 *
	 * @var  string
	 */
	private $exif_data = null;

	/**
	 * Configuration options
	 *
	 * @var  array<string,mixed>
	 */
	private $config = array();

	/**
	 * Constructor
	 *
	 * @param   string  $image_source  Path to image
	 * @param   array<string,mixed>   $config        Optional configurations
	 * @return  void
	 * @throws Exception
	 */
	public function __construct(string $image_source = null, array $config = array())
	{
		$this->source = $image_source;
		$this->config = $config;

		if (!$this->checkPackageRequirements('gd'))
		{
			throw new Exception('[ERROR] You are missing the required PHP package GD.');
		}

		// check to see if we have an image to work with
		if (is_null($this->source))
		{
			throw new Exception('[ERROR] Image Source not set.');
		}

		//open image
		if (!$this->openImage())
		{
			throw new Exception('[ERROR] Invalid/corrupted image file');
		}
	}

	/**
	 * Set the image type
	 *
	 * @param   string  $type  Image type to set
	 * @return  void
	 */
	public function setImageType(string $type): void
	{
		if ($type)
		{
			$this->image_type = $type;
		}

		if ($this->image_type == IMAGETYPE_PNG && $this->resource)
		{
			imagealphablending($this->resource, false);
			imagesavealpha($this->resource, true);
		}
	}

	/**
	 * Check if a required package is installed
	 *
	 * @param   string  $package  Package name
	 * @return  bool
	 */
	private function checkPackageRequirements(string $package): bool
	{
		$installed_exts = get_loaded_extensions();

		if (!in_array($package, $installed_exts))
		{
			return false;
		}

		return true;
	}

	/**
	 * Open an image and get it's type (png, jpg, gif)
	 *
	 * @return  bool
	 */
	private function openImage(): bool
	{
		$image_atts = getimagesize($this->source);
		if (empty($image_atts))
		{
			return false;
		}

		switch ($image_atts['mime'])
		{
			case 'image/jpeg':
				$this->image_type = IMAGETYPE_JPEG;
				$this->resource   = imagecreatefromjpeg($this->source);
			break;
			case 'image/gif':
				$this->image_type = IMAGETYPE_GIF;
				$this->resource   = imagecreatefromgif($this->source);
			break;
			case 'image/png':
			case 'image/x-png':
				$this->image_type = IMAGETYPE_PNG;
				$this->resource   = imagecreatefrompng($this->source);
			break;
			default:
				return false;
			break;
		}

		if ($this->image_type == IMAGETYPE_PNG)
		{
			imagesavealpha($this->resource, true);
			imagealphablending($this->resource, false);
		}

		if (isset($this->config['auto_rotate']) && $this->config['auto_rotate'] == true)
		{
			$this->autoRotate();
		}

		if (!empty($this->resource))
		{
			return true;
		}
		return false;
	}

	/**
	 * Image Rotation
	 *
	 * @return  void
	 * @throws Exception
	 */
	public function autoRotate(): void
	{
		if (!$this->checkPackageRequirements('exif'))
		{
			throw new Exception('You need the PHP exif library installed to rotate image based on Exif Orientation value.');
		}

		if ($this->image_type == IMAGETYPE_JPEG)
		{
			try
			{
				$this->exif_data = exif_read_data($this->source);
			}
			catch (Exception $e)
			{
				$this->exif_data = array();
			}

			if (isset($this->exif_data['Orientation']))
			{
				switch ($this->exif_data['Orientation'])
				{
					case 2:
						$this->flip(true, false);
						break;

					case 3:
						$this->rotate(180);
						break;

					case 4:
						$this->flip(false, true);
						break;

					case 5:
						$this->rotate(270);
						$this->flip(true, false);
						break;

					case 6:
						$this->rotate(270);
						break;

					case 7:
						$this-rotate(90);
						$this->flip(true, false);
						break;

					case 8:
						$this->rotate(90);
						break;
				}
			}
		}
	}

	/**
	 * Image Flip
	 *
	 * @param   int  $rotation    Degrees to rotate
	 * @param   int  $background  Point to rotate from
	 * @return  void
	 */
	public function rotate(int $rotation = 0, int $background = 0): void
	{
		if (empty($this->resource))
		{
			return;
		}
		$resource = imagerotate($this->resource, $rotation, $background);
		imagedestroy($this->resource);
		$this->resource = $resource;
	}

	/**
	 * Image Flip
	 *
	 * @param   bool  $flip_horizontal  Flip the image horizontally?
	 * @param   bool  $flip_vertical    Flip the image vertically?
	 * @return  void
	 */
	public function flip(bool $flip_horizontal = true, bool $flip_vertical = false): void
	{
		if (empty($this->resource))
		{
			return;
		}

		$resource = $this->resource;
		$width = imagesx($resource);
		$height = imagesy($resource);
		$new_resource = imagecreatetruecolor($width, $height);

		for ($x=0; $x<$width; $x++)
		{
			for ($y=0; $y<$height; $y++)
			{
				if ($flip_horizontal && $flip_vertical)
				{
					imagecopy($new_resource, $resource, $width-$x-1, $height-$y-1, $x, $y, 1, 1);
				}
				else if ($flip_horizontal)
				{
					imagecopy($new_resource, $resource, $width-$x-1, $y, $x, $y, 1, 1);
				}
				else if ($flip_vertical)
				{
					imagecopy($new_resource, $resource, $x, $height-$y-1, $x, $y, 1, 1);
				}
			}
		}

		$this->resource = $new_resource;
		imagedestroy($resource);
	}

	/**
	 * Image Crop
	 *
	 * @param   int  $top     Top point to crop from
	 * @param   int  $right   Right point to crop from
	 * @param   int  $bottom  Bottom point to crop from
	 * @param   int  $left    Left point to crop from
	 * @return  void
	 */
	public function crop(int $top, int $right = 0, int $bottom = 0, int $left = 0): void
	{
		if (empty($this->resource))
		{
			return;
		}
		$width      = imagesx($this->resource);
		$height     = imagesy($this->resource);
		$new_width  = $width - ($left + $right);
		$new_height = $height - ($top + $bottom);

		$resource = imagecreatetruecolor($new_width, $new_height);
		imagecopy($resource, $this->resource, 0, 0, $left, $top, $new_width, $new_height);

		imagedestroy($this->resource);
		$this->resource = $resource;
	}

	/**
	 * Image Resize
	 *
	 * @param   int  $new_dimension  Size to resize image to
	 * @param   bool  $use_height     Use the height as the baseline? (uses width by default)
	 * @param   bool  $squared        Make the image square?
	 * @param   bool  $resample       Resample the image?
	 * @return  void
	 */
	public function resize(int $new_dimension, bool $use_height = false, bool $squared = false, bool $resample = true): void
	{
		if (empty($this->resource))
		{
			return;
		}
		$percent = false;
		$width   = imagesx($this->resource);
		$height  = imagesy($this->resource);
		$w       = $width;
		$h       = $height;
		$x       = 0;
		$y       = 0;

		if (($new_dimension > $width && !$use_height) || ($new_dimension > $height && $use_height))
		{
			return;
		}

		if ($new_dimension < 1)
		{
			$percent = $new_dimension;
		}
		elseif (substr($new_dimension, -1) == '%')
		{
			$percent = (substr($new_dimension, 0, -1) / 100);
		}

		if ($percent !== false)
		{
			$new_dimension = $use_height ? ($height * $percent) : ($width * $percent);
			$new_dimension = round($new_dimension);
		}

		if ($squared)
		{
			$new_w = $new_dimension;
			$new_h = $new_dimension;
			if (!$use_height)
			{
				$x = ceil(($width - $height) / 2);
				$w = $height;
				$h = $height;
			}
			else
			{
				$y = ceil(($height - $width) / 2);
				$w = $width;
				$h = $width;
			}
		}
		else
		{
			if (!empty($this->config['resize_by']) && $this->config['resize_by'] == 'largest')
			{
				// find whatever is larger and use it for resizing
				$use_height = false;
				if ($height > $width)
				{
					$use_height = true;
				}
			}

			if (!$use_height)
			{
				$new_w = $new_dimension;
				$new_h = floor($height * ($new_w / $width));
			}
			else
			{
				$new_h = $new_dimension;
				$new_w = floor($width * ($new_h / $height));
			}
		}

		$resource = imagecreatetruecolor($new_w, $new_h);

		$transparencyIndex = imagecolortransparent($this->resource);
		$transparencyColor = array('red' => 255, 'green' => 255, 'blue' => 255);
		if ($transparencyIndex >= 0)
		{
			$transparencyColor = imagecolorsforindex($this->resource, $transparencyIndex);
		}
		$transparencyIndex = imagecolorallocate(
			$resource,
			$transparencyColor['red'],
			$transparencyColor['green'],
			$transparencyColor['blue']
		);
		imagefill($resource, 0, 0, $transparencyIndex);
		imagecolortransparent($resource, $transparencyIndex);

		if ($resample)
		{
			imagecopyresampled($resource, $this->resource, 0, 0, $x, $y, $new_w, $new_h, $w, $h);
		}
		else
		{
			imagecopyresized($resource, $this->resource, 0, 0, $x, $y, $new_w, $new_h, $w, $h);
		}

		imagedestroy($this->resource);
		$this->resource = $resource;
	}

	/**
	 * Image Geo Location Data
	 *
	 * @return array<string,string>
	 * @throws Exception
	 */
	public function getGeoLocation(): array
	{
		if (!$this->checkPackageRequirements('exif'))
		{
			throw new Exception('You need the PHP exif library installed to rotate image based on Exif Orientation value.');
		}

		try
		{
			$this->exif_data = exif_read_data($this->source);
		}
		catch (Exception $e)
		{
			$this->exif_data = array();
		}

		if (isset($this->exif_data['GPSLatitude']))
		{
			$lat      = $this->exif_data['GPSLatitude'];
			$lat_dir  = $this->exif_data['GPSLatitudeRef'];
			$long     = $this->exif_data['GPSLongitude'];
			$long_dir = $this->exif_data['GPSLongitudeRef'];

			$latitude  = $this->geo_single_fracs2dec($lat);
			$longitude = $this->geo_single_fracs2dec($long);
			$latitude_formatted  = $this->geo_pretty_fracs2dec($lat) . $lat_dir;
			$longitude_formatted = $this->geo_pretty_fracs2dec($long) . $long_dir;

			if ($lat_dir == 'S')
			{
				$latitude *= -1;
			}

			if ($long_dir == 'W')
			{
				$longitude *= -1;
			}

			$geo = array(
				'latitude'  => $latitude,
				'longitude' => $longitude,
				'latitude_formatted'  => $latitude_formatted,
				'longitude_formatted' => $longitude_formatted
			);
		}
		else
		{
			$geo = array();
		}

		return $geo;
	}

	/**
	 * Convert a fraction to decimal
	 *
	 * @param   string   $str  Fraction to convert
	 * @return  int
	 */
	private function geo_frac2dec(string $str): int
	{
		list($n, $d) = explode('/', $str);

		if (!empty($d))
		{
			return $n / $d;
		}

		return $str;
	}

	/**
	 * Convert fractions to decimals with formatting
	 *
	 * @param   array   $fracs  Fractions to convert
	 * @return  string
	 */
	private function geo_pretty_fracs2dec($fracs): string
	{
		return $this->geo_frac2dec($fracs[0]) . '&deg; ' . $this->geo_frac2dec($fracs[1]) . '&prime; ' . $this->geo_frac2dec($fracs[2]) . '&Prime; ';
	}

	/**
	 * Convert fractions to decimals
	 *
	 * @param   array    $fracs  Fractions to convert
	 * @return  int
	 */
	private function geo_single_fracs2dec($fracs): int
	{
		return $this->geo_frac2dec($fracs[0]) + $this->geo_frac2dec($fracs[1]) / 60 + $this->geo_frac2dec($fracs[2]) / 3600;
	}

	/**
	 * Display an image
	 *
	 * @return  void
	 */
	public function display(): void
	{
		$image_atts = getimagesize($this->source);
		header('Content-type: ' . $image_atts['mime']);
		$this->output(null);
	}

	/**
	 * Display an image inline
	 *
	 * @return  string
	 **/
	public function inline(): string
	{
		// Start buffer and grab output
		ob_start();
		$this->output(null);
		$image_data = ob_get_contents();
		ob_end_clean();

		// Encode and build data uri
		$base64 = base64_encode($image_data);
		$image_atts = getimagesize($this->source);

		return 'data:' . $image_atts['mime'] . ';base64,' . $base64;
	}

	/**
	 * Save an image
	 *
	 * @param   string   $save_path   Path to save image
	 * @param   bool  $make_paths  Allow for path generation?
	 * @return  void
	 */
	public function save(string $save_path = null, bool $make_paths = false): void
	{
		$path = $this->source;

		if (!is_null($save_path))
		{
			$info = pathinfo($save_path);

			if ($make_paths)
			{
				if (!Storage::disk('public')->exists($info['dirname']))
				{
					Storage::disk('public')->makeDirectory($info['dirname']);
				}
			}

			if (!is_dir($info['dirname']) && $make_paths == false)
			{
				throw new Exception('You must supply a valid path or allow save function to create recursive path');
			}

			$path = $save_path;
		}

		$this->output($path);
	}

	/**
	 * Generate an image and save to a location
	 *
	 * @param   string  $save_path  Path to save image
	 * @return  void
	 */
	private function output(string $save_path): void
	{
		if ($this->resource != null)
		{
			switch ($this->image_type)
			{
				case IMAGETYPE_PNG:
					imagepng($this->resource, $save_path);
					break;

				case IMAGETYPE_GIF:
					imagegif($this->resource, $save_path);
					break;

				case IMAGETYPE_JPEG:
					imagejpeg($this->resource, $save_path);
					break;
			}
		}
	}
}
