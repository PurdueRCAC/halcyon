<?php

namespace App\Listeners\Captcha\Image;

use Illuminate\Events\Dispatcher;
use App\Modules\Core\Events\CaptchaIsRendering;
use App\Modules\Core\Events\ValidateCaptcha;

/**
 * Generate Image CAPTCHAs
 */
class Image
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(CaptchaIsRendering::class, self::class . '@handleRendering');
		$events->listen(ValidateCaptcha::class, self::class . '@handleValidation');
	}

	/**
	 * Image background color
	 *
	 * @var  string
	 */
	private $_bgColor = '#ffffff';

	/**
	 * Text color
	 *
	 * @var  string
	 */
	private $_textColor = '#2c8007';

	/**
	 * Displays either a CAPTCHA image or form field
	 *
	 * @param   CaptchaIsRendering $event
	 * @return  void
	 */
	public function handleRendering(CaptchaIsRendering $event): void
	{
		if (request()->input('showCaptcha', ''))
		{
			$this->render();
			return;
		}

		$attr = $event->attributes;

		app('view')->addNamespace(
			'listener.captcha.image',
			app_path() . '/Listeners/Captcha/Image/views'
		);

		app('translator')->addNamespace(
			'listener.captcha.image',
			app_path() . '/Listeners/Captcha/Image/lang'
		);

		$event->setContent(view('listener.captcha.image::index', [
			//'name' => $name,
			//'id'    => $attr['id'],
			//'total' => $GLOBALS['totalCaptchas'],
			'atts' => $attr,
		]));
	}

	/**
	 * Displays a CAPTCHA image
	 *
	 * @return  void
	 */
	private function render(): void
	{
		$imageFunction = '_createImage' . config('listener.captcha.image.imageFunction', 'Adv');
		$imageFunction = (!method_exists($this, $imageFunction)) ? '_createImageAdv' : $imageFunction;

		$this->$imageFunction();
		exit;
	}

	/**
	 * Checks if a CAPTCHA response is valid
	 *
	 * @param   string   $word        Supplied CAPTCHA response to check
	 * @param   string   $instanceNo  CAPTCHA instance number
	 * @return  bool  True if valid
	 */
	private function _confirm($word, $instanceNo=''): bool
	{
		$securiy_code = request()->session()->get('captcha.key');

		if ($word && $word == $securiy_code)
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks for a CAPTCHA response and Calls the CAPTCHA validity check
	 *
	 * @param   ValidateCaptcha $event Answer provided by user. Not needed for the Recaptcha implementation
	 * @return  bool  True if valid CAPTCHA response
	 */
	public function handleValidation(ValidateCaptcha $event): bool
	{
		$imgCatchaTxt     = strtolower(request('imgCaptchaTxt'));
		$imgCatchaTxtInst = 1; //request('imgCaptchaTxtInst');

		if (!$imgCatchaTxtInst || !$imgCatchaTxt)
		{
			$event->valid = false;
			return false;
		}

		if ($imgCatchaTxt
		 && $this->_confirm($imgCatchaTxt, $imgCatchaTxtInst))
		{
			$event->valid = true;
		}

		return false;
	}

	/**
	 * Sets some internal variables
	 *
	 * @return  void
	 */
	private function _setColors(): void
	{
		$this->_bgColor   = config('listener.captcha.image.bgColor', $this->_bgColor);
		$this->_textColor = config('listener.captcha.image.textColor', $this->_textColor);
	}

	/**
	 * Creates a distorted image
	 *
	 * @return  void
	 */
	private function _createImageAdv(): void
	{
		$alphabet = "0123456789abcdefghijklmnopqrstuvwxyz";
		$allowed_symbols = "23456789abcdeghkmnpqsuvxyz";

		$length = 5;
		$width = 120;
		$height = 60;
		$fluctuation_amplitude = 5;
		$no_spaces = false;

		$this->_setColors();

		$foreground_color = $this->_hexToRgb($this->_textColor);
		$background_color = $this->_hexToRgb($this->_bgColor);

		$jpeg_quality = 90;

		$alphabet_length = strlen($alphabet);

		do
		{
			// generating random keystring
			while (true)
			{
				$this->keystring = '';
				for ($i=0; $i<$length; $i++)
				{
					$pos = mt_rand(0, strlen($allowed_symbols)-1);
					$this->keystring .= substr($allowed_symbols, $pos, 1);
				}
				if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $this->keystring))
				{
					break;
				}
			}

			$font_file = $font_file = __DIR__ . '/assets/fonts/adlibBT.png';
			$font = imagecreatefrompng($font_file);
			imagealphablending($font, true);
			$fontfile_width = imagesx($font);
			$fontfile_height = imagesy($font)-1;
			$font_metrics = array();
			$symbol = 0;
			$reading_symbol = false;

			// loading font
			for ($i=0; $i<$fontfile_width && $symbol<$alphabet_length; $i++)
			{
				$transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

				if (!$reading_symbol && !$transparent)
				{
					$font_metrics[substr($alphabet, $symbol, 1)] = array('start'=>$i);
					$reading_symbol = true;
					continue;
				}

				if ($reading_symbol && $transparent)
				{
					$font_metrics[substr($alphabet, $symbol, 1)]['end'] = $i;
					$reading_symbol = false;
					$symbol++;
					continue;
				}
			}

			$img = imagecreatetruecolor($width, $height);
			imagealphablending($img, true);
			$white = imagecolorallocate($img, 255, 255, 255);
			$black = imagecolorallocate($img, 0, 0, 0);

			imagefilledrectangle($img, 0, 0, $width-1, $height-1, $white);

			// draw text
			$x = 1;
			for ($i=0; $i<$length; $i++)
			{
				$m = $font_metrics[substr($this->keystring, $i, 1)];

				$y = mt_rand(-$fluctuation_amplitude, $fluctuation_amplitude)+($height-$fontfile_height)/2+2;

				if ($no_spaces)
				{
					$shift = 0;
					if ($i>0)
					{
						$shift = 10000;
						for ($sy=7; $sy<$fontfile_height-20; $sy+=1)
						{
							for ($sx=$m['start']-1; $sx<$m['end']; $sx+=1)
							{
								$rgb = imagecolorat($font, $sx, $sy);
								$opacity = $rgb>>24;
								if ($opacity < 127)
								{
									$left = $sx-$m['start']+$x;
									$py = $sy+$y;
									if ($py > $height)
									{
										break;
									}
									for ($px=min($left, $width-1); $px>$left-12 && $px>=0; $px-=1)
									{
										$color = imagecolorat($img, $px, $py) & 0xff;
										if ($color+$opacity < 190)
										{
											if ($shift > $left-$px)
											{
												$shift = $left-$px;
											}
											break;
										}
									}
									break;
								}
							}
						}
						if ($shift == 10000)
						{
							$shift = mt_rand(4, 6);
						}

					}
				}
				else
				{
					$shift = 1;
				}
				imagecopy($img, $font, $x-$shift, $y, $m['start'], 1, $m['end']-$m['start'], $fontfile_height);
				$x += $m['end']-$m['start']-$shift;
			}
		}
		while ($x >= $width-10); // while not fit in canvas

		$center = $x/2;

		$img2 = imagecreatetruecolor($width, $height);
		$foreground = imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$background = imagecolorallocate($img2, $background_color[0], $background_color[1], $background_color[2]);
		imagefilledrectangle($img2, 0, 0, $width-1, $height-1, $background);
		imagefilledrectangle($img2, 0, $height, $width-1, $height+12, $foreground);

		// periods
		$rand1 = mt_rand(750000, 1200000)/10000000;
		$rand2 = mt_rand(750000, 1200000)/10000000;
		$rand3 = mt_rand(750000, 1200000)/10000000;
		$rand4 = mt_rand(750000, 1200000)/10000000;
		// phases
		$rand5 = mt_rand(0, 31415926)/10000000;
		$rand6 = mt_rand(0, 31415926)/10000000;
		$rand7 = mt_rand(0, 31415926)/10000000;
		$rand8 = mt_rand(0, 31415926)/10000000;
		// amplitudes
		$rand9 = mt_rand(330, 420)/110;
		$rand10 = mt_rand(330, 450)/110;

		// wave distortion
		for ($x=0; $x<$width; $x++)
		{
			for ($y=0; $y<$height; $y++)
			{
				$sx = $x+(sin($x*$rand1+$rand5)+sin($y*$rand3+$rand6))*$rand9-$width/2+$center+1;
				$sy = $y+(sin($x*$rand2+$rand7)+sin($y*$rand4+$rand8))*$rand10;

				if ($sx<0 || $sy<0 || $sx>=$width-1 || $sy>=$height-1)
				{
					continue;
				}
				else
				{
					$color = imagecolorat($img, $sx, $sy) & 0xFF;
					$color_x = imagecolorat($img, $sx+1, $sy) & 0xFF;
					$color_y = imagecolorat($img, $sx, $sy+1) & 0xFF;
					$color_xy = imagecolorat($img, $sx+1, $sy+1) & 0xFF;
				}

				if ($color==255 && $color_x==255 && $color_y==255 && $color_xy==255)
				{
					continue;
				}
				else if ($color==0 && $color_x==0 && $color_y==0 && $color_xy==0)
				{
					$newred = $foreground_color[0];
					$newgreen = $foreground_color[1];
					$newblue = $foreground_color[2];
				}
				else
				{
					$frsx = $sx-floor($sx);
					$frsy = $sy-floor($sy);
					$frsx1 = 1-$frsx;
					$frsy1 = 1-$frsy;

					$newcolor = (
						$color*$frsx1*$frsy1+
						$color_x*$frsx*$frsy1+
						$color_y*$frsx1*$frsy+
						$color_xy*$frsx*$frsy
					);

					if ($newcolor > 255)
					{
						$newcolor = 255;
					}
					$newcolor = $newcolor/255;
					$newcolor0 = 1-$newcolor;

					$newred = $newcolor0*$foreground_color[0]+$newcolor*$background_color[0];
					$newgreen = $newcolor0*$foreground_color[1]+$newcolor*$background_color[1];
					$newblue = $newcolor0*$foreground_color[2]+$newcolor*$background_color[2];
				}

				imagesetpixel($img2, $x, $y, imagecolorallocate($img2, $newred, $newgreen, $newblue));
			}
		}

		$security_code = strtolower(str_replace(' ', '', trim($this->keystring)));

		//Set the session to store the security code
		request()->session()->put('captcha', [
			'key' => $security_code
		]);
		$width = 120;
		$height = 40;

		if (ob_get_level() > 0)
		{
			ob_clean();
		}

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');

		if (function_exists('imagejpeg'))
		{
			header('Content-Type: image/jpeg');
			imagejpeg($img2, null, $jpeg_quality);
		}
		else if (function_exists('imagegif'))
		{
			header('Content-Type: image/gif');
			imagegif($img2);
		}
		else if (function_exists('imagepng'))
		{
			header('Content-Type: image/x-png');
			imagepng($img2);
		}

		exit();
	}

	/**
	 * Creates a plain letter image
	 *
	 * @return  void
	 */
	private function _createImagePlain(): void
	{
		// Let's generate a totally random string using md5
		$md5_hash = md5(rand(0, 999));

		// We don't need a 32 character long string so we trim it down to 5
		$security_code = str_replace(array("0", "O", "o"), array("p"), substr($md5_hash, 15, 5));

		// Set the session to store the security code
		request()->session()->put('captcha', [
			'key' => $security_code
		]);

		$width = 120;
		$height = 40;
		$image = imagecreate($width, $height);
		$this->_setColors();
		$foreground_color = $this->_hexToRgb($this->_textColor);
		$background_color = $this->_hexToRgb($this->_bgColor);

		// We are making three colors, white, black and gray
		$white = imagecolorallocate($image, $foreground_color[0], $foreground_color[1], $foreground_color[2]);
		$black = imagecolorallocate($image, $background_color[0], $background_color[1], $background_color[2]);
		$grey  = imagecolorallocate($image, 204, 204, 204);

		// Make the background black
		imagefill($image, 0, 0, $black);

		$size = 10;
		$this->ly = (int)(2.4 * $size);
		$x = 20;
		for ($i=0; $i<strlen($security_code); $i++)
		{
			$angle = rand(-45, 45);
			$y = intval(rand((int)($size * 1.5), (int)($this->ly - ($size / 7))));

			@imagettftext($image, $size, $angle, $x + (int)($size / 15), $y, $white, __DIR__ . '/assets/fonts/adlibBT.TTF', $security_code[$i]);
			$x += ($size *2);
		}

		if (ob_get_level() > 0)
		{
			ob_clean();
		}
		header('Content-type: image/png');
		imagepng($image);
	}

	/**
	 * Converts HEX color to RGB
	 *
	 * @param   string  $hex  Hex value to convert
	 * @return  string
	 */
	private function _hexToRgb($hex): string
	{
		$hex = preg_replace("/#/", '', $hex);
		$color = array();

		if (strlen($hex) == 3)
		{
			$color['r'] = hexdec(substr($hex, 0, 1) . $r);
			$color['g'] = hexdec(substr($hex, 1, 1) . $g);
			$color['b'] = hexdec(substr($hex, 2, 1) . $b);
		}
		else if (strlen($hex) == 6)
		{
			$color['r'] = hexdec(substr($hex, 0, 2));
			$color['g'] = hexdec(substr($hex, 2, 2));
			$color['b'] = hexdec(substr($hex, 4, 2));
		}

		return array_values($color);
	}

	/**
	 * Converts RGB color to HEX
	 *
	 * @param   string  $r  R color to convert
	 * @param   string  $g  G color to convert
	 * @param   string  $b  B color to convert
	 * @return  string
	 */
	private function _rgbToHex($r, $g, $b): string
	{
		$hex  = '#';
		$hex .= dechex($r);
		$hex .= dechex($g);
		$hex .= dechex($b);

		return $hex;
	}
}
