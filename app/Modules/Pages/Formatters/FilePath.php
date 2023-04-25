<?php
namespace App\Modules\Pages\Formatters;

use Closure;

/**
 * Parse @file() macros
 */
class FilePath
{
	/**
	 * Handle content
	 */
	public function handle(string $content, Closure $next): string
	{
		// Expression to search for
		$regex = "/@file\(([^\)]*)\)/i";

		// Find all instances of plugin and put in $matches
		preg_match_all($regex, $content, $matches, PREG_SET_ORDER);

		if ($matches)
		{
			foreach ($matches as $match)
			{
				// Trim whitespace
				$path = trim($match[1]);

				// Trim quotes
				$path = trim($path, '"\'');

				// Remove the "/files" segment
				if (substr($path, 0, 6) == '/files')
				{
					$path = substr($path, 6);
				}
				$path = ltrim($path, '/');

				// Get the full site path
				$text = \asset('files/' . $path);

				$content = str_replace($match[0], $text, $content);
			}
		}

		return $next($content);
	}
}
