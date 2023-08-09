<?php

if (! function_exists('parse_ini_string_m'))
{
	/**
	 * Parse an INI file into an array
	 *
	 * @param   string  $str
	 * @return  array<string,mixed>|false
	 */
	function parse_ini_string_m($str)
	{
		if (empty($str))
		{
			return false;
		}

		$ret = array();
		$section = '';
		$lines = explode("\n", $str);
		$commentchars = array('#', ';', '!');

		foreach ($lines as $line)
		{
			$line = trim($line);

			if ($line == '' || in_array($line[0], $commentchars))
			{
				continue;
			}
			elseif ($line[0] == '[' && $line[strlen($line) - 1] == ']')
			{
				$section = trim($line, '[]');
				$ret[$section] = array();
			}
			else
			{
				if (preg_match('/^(\S+)\s+(\S.*)$/', $line, $parts))
				{
					$key = rtrim($parts[1]);
					$value = ltrim($parts[2]);
				}
				elseif (strpos($line, '='))
				{
					$parts = explode('=', $line, 2);
					$key = rtrim($parts[0]);
					$value = ltrim($parts[1]);
				}
				else
				{
					continue;
				}

				$parsed_value = '';

				if ($value[0] == '"')
				{
					if (preg_match('/[^\\\]"/', $value, $matches, PREG_OFFSET_CAPTURE))
					{
						$parsed_value = substr($value, 1, $matches[0][1]);
					}
				}
				elseif ($value[0] == "'")
				{
					if (preg_match("/[^\\\]'/", $value, $matches, PREG_OFFSET_CAPTURE))
					{
						$parsed_value = substr($value, 1, $matches[0][1]);
					}
				}
				else
				{
					$parsed_value = $value;

					foreach ($commentchars as $commentchar)
					{
						if ($pos = strpos($parsed_value, $commentchar))
						{
							$parsed_value = substr($parsed_value, 0, $pos);
						}
					}
				}

				if ($section == '')
				{
					$ret[$key] = $parsed_value;
				}
				else
				{
					$ret[$section][$key] = $parsed_value;
				}
			}
		}

		return $ret;
	}
}

if (! function_exists('conf'))
{
	/**
	 * Get the path to the public folder.
	 *
	 * @param  string  $service
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function conf($service, $key, $default = null)
	{
		static $params;

		if (!isset($params[$service]))
		{
			$params[$service] = array();

			$path = '/usr/site/rcac/secure/' . $service . '.conf';

			if (is_file($path))
			{
				$params[$service] = parse_ini_string_m(file_get_contents($path));
			}
		}

		if (isset($params[$service][$key]))
		{
			return $params[$service][$key];
		}

		return $default;
	}
}

if (! function_exists('timestamped_asset'))
{
	/**
	 * Get a timestamped asset
	 *
	 * This helps with browser cache busting when files change.
	 *
	 * @param  string  $path
	 * @return string
	 */
	function timestamped_asset($path)
	{
		// Check if the URL is absolute
		if (substr($path, 0, strlen('http')) == 'http'
		 || substr($path, 0, strlen('://')) == '://'
		 || substr($path, 0, strlen('//')) == '//')
		{
			return $path;
		}

		$p = public_path(ltrim($path, '/'));

		if (file_exists($p))
		{
			$path = rtrim($path, '?');
			$path .= '?v=' . filemtime($p);
		}

		return asset($path);
	}
}
