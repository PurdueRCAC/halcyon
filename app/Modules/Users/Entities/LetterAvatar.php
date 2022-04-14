<?php
namespace App\Modules\Users\Entities;

use Exception;

/**
 * Generate an image with the initials from a string.
 */
class LetterAvatar
{
	/**
	 * Image Type PNG
	 */
	const MIME_TYPE_PNG = 'image/png';

	/**
	 * Image Type JPEG
	 */
	const MIME_TYPE_JPEG = 'image/jpeg';

	/**
	 * @var  string
	 */
	private $string;

	/**
	 * @var  integer
	 */
	private $size;

	/**
	 * @var string
	 */
	private $backgroundColor;

	/**
	 * @var string
	 */
	private $foregroundColor;

	/**
	 * Cnstructor.
	 * 
	 * @param string $string
	 * @param int    $size
	 * @return void
	 */
	public function __construct(string $string, int $size = 50)
	{
		$this->setString($string);
		$this->setSize($size);
	}

	/**
	 * Set the image size
	 *
	 * @param   integer  $size
	 * @return  object
	 */
	public function setSize($size)
	{
		$this->size = (int)$size;

		return $this;
	}

	/**
	 * Generate a hash fron the original string
	 *
	 * @param   string  $string
	 * @return  object
	 */
	public function setString($string)
	{
		if (!$string)
		{
			throw new Exception('The string cannot be empty.');
		}

		$this->string = $string;

		return $this;
	}

	/**
	 * Color in RGB format (example: #FFFFFF)
	 * 
	 * @param string $backgroundColor
	 * @param string $foregroundColor
	 * @return object
	 */
	public function setColor($backgroundColor, $foregroundColor)
	{
		$this->backgroundColor = $this->hexToRgb($backgroundColor);
		$this->foregroundColor = $this->hexToRgb($foregroundColor);

		return $this;
	}

	/**
	 * Convert hex to RGB
	 * 
	 * @param string $kex
	 * @return array
	 */
	private function hexToRgb($hex)
	{
		$hex      = str_replace('#', '', $hex);
		$length   = strlen($hex);
		$rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
		$rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
		$rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));

		return $rgb;
	}

	/**
	 * Convert a string to a color
	 * 
	 * @param string $string
	 * @return string
	 */
	private function stringToColor(string $string, $adjustPercent = 0.8): array
	{
		// Random color
		$rgb = substr(dechex(crc32($string)), 0, 6);

		list($R16, $G16, $B16) = str_split($rgb, 2);

		$R16 = hexdec($R16);
		$adjustAmount = ceil(($adjustPercent < 0 ? $R16 : 255 - $R16) * $adjustPercent);
		$R = str_pad(dechex($R16 + $adjustAmount), 2, '0', STR_PAD_LEFT);

		$G16 = hexdec($G16);
		$adjustAmount = ceil(($adjustPercent < 0 ? $G16 : 255 - $G16) * $adjustPercent);
		$G = str_pad(dechex($G16 + $adjustAmount), 2, '0', STR_PAD_LEFT);

		$B16 = hexdec($B16);
		$adjustAmount = ceil(($adjustPercent < 0 ? $B16 : 255 - $B16) * $adjustPercent);
		$B = str_pad(dechex($B16 + $adjustAmount), 2, '0', STR_PAD_LEFT);

		//$R = sprintf('%02X', floor(hexdec($R16) / $darker));
		//$G = sprintf('%02X', floor(hexdec($G16) / $darker));
		//$B = sprintf('%02X', floor(hexdec($B16) / $darker));

		return $this->hexToRgb('#' . $R . $G . $B);
	}

	/**
	 * Generate the image
	 *
	 * @return  void
	 */
	private function generate()
	{
		$initials = $this->getInitials($this->string);
		$this->backgroundColor = $this->backgroundColor ?: $this->stringToColor($this->string, 0.8);
		$this->foregroundColor = $this->foregroundColor ?: $this->stringToColor($this->string, 0.4); //$this->hexToRgb('#fafafa');

		$pixelRatio = round($this->size / 5);

		// Prepare the image
		$image = imagecreatetruecolor($pixelRatio * 5, $pixelRatio * 5);

		// Prepage the color
		$backgroundColor = imagecolorallocate(
			$image,
			$this->backgroundColor['r'],
			$this->backgroundColor['g'],
			$this->backgroundColor['b']
		);
		imagefilledrectangle($image, 0, 0, $this->size, $this->size, $backgroundColor);

		// Allocate A Color For The Text
		$foregroundColor = imagecolorallocate(
			$image,
			$this->foregroundColor['r'],
			$this->foregroundColor['g'],
			$this->foregroundColor['b']
		);

		$rnd = ceil($this->size / 20);

		$fontsize = round($this->size * 0.33); //ceil(($this->size / 3) + $rnd);
		$fontpath = __DIR__ . '/fonts/arial.ttf';

		/*
		0 = lower left corner, X position
		1 = lower left corner, Y position
		2 = lower right corner, X position
		3 = lower right corner, Y position
		4 = upper right corner, X position
		5 = upper right corner, Y position
		6 = upper left corner, X position
		7 = upper left corner, Y position
		*/
		$tb = imagettfbbox($fontsize, 0, $fontpath, $initials);

		// Determine offset of text
		$left_offset = ($tb[2] - $tb[0]) / 2;
		$top_offset = ($tb[1] - $tb[5]) / 2;

		// Generate coordinates
		$x = ($this->size / 2) - $left_offset;
		$y = ($this->size / 2) + $top_offset;

		// Print Text On Image
		imagettftext($image, $fontsize, 0, $x, $y, $foregroundColor, $fontpath, $initials);

		imagepng($image);
	}

	/**
	 * Get initials from string
	 * 
	 * @param string $name
	 * @return string
	 */
	private function getInitials(string $name): string
	{
		$nameParts = $this->breakName($name);

		if (!$nameParts)
		{
			return '';
		}

		$secondLetter = count($nameParts) > 1 ? $this->getFirstLetter(end($nameParts)) : '';

		return $this->getFirstLetter($nameParts[0]) . $secondLetter;
	}

	/**
	 * Explodes Name into an array.
	 * The function will check if a part is , or blank
	 *
	 * @param string $name Name to be broken up
	 * @return array Name broken up to an array
	 */
	private function breakName(string $name): array
	{
		$words = explode(' ', $name);
		$words = array_filter($words, function($word)
		{
			return ($word !== '' && $word !== ',');
		});
		return array_values($words);
	}

	/**
	 * Get the first letter from a word
	 * 
	 * @param string $word
	 * @return string
	 */
	private function getFirstLetter(string $word): string
	{
		return mb_strtoupper(trim(mb_substr($word, 0, 1, 'UTF-8')));
	}

	/**
	 * Save the generated Letter-Avatar as a file
	 *
	 * @param string $path
	 * @param string $mimetype
	 * @return bool
	 */
	public function saveAs($path, $mimetype = self::MIME_TYPE_PNG): bool
	{
		if (empty($path))
		{
			return false;
		}

		/*if (!Storage::disk('public')->exists($path))
		{
			Storage::disk('public')->makeDirectory($path, true);
		}*/
		$dir = dirname($path);
		if (!is_dir($dir))
		{
			@mkdir($dir, 0755, true);
		}

		return is_int(@file_put_contents($path, $this->data()));
	}

	/**
	 * Display the generated image
	 *
	 * @return  void
	 */
	public function display()
	{
		header("Content-Type: " . self::MIME_TYPE_PNG);
		$this->generate();
	}

	/**
	 * Get image data
	 *
	 * @return  string
	 */
	public function data()
	{
		ob_start();
		$this->generate();
		$imageData = ob_get_contents();
		ob_end_clean();

		return $imageData;
	}

	/**
	 * Get image data as a data-uri
	 *
	 * @return  string
	 */
	public function dataUri(): string
	{
		return sprintf('data:image/png;base64,%s', base64_encode($this->data()));
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return (string)$this->dataUri();
	}
}
