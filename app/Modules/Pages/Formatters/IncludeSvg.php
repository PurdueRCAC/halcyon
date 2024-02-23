<?php
namespace App\Modules\Pages\Formatters;

use App\Halcyon\Utility\Number;
use Closure;

/**
 * Parse @svg() macros
 */
class IncludeSvg
{
	/**
	 * Handle content
	 */
	public function handle(string $content, Closure $next): string
	{
		// Expression to search for
		$regex = "/@svg\(([^\)]*)\)/i";

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

				// Get the absolute file path
				$text = \storage_path('app/public/' . $path);

				if (is_file($text))
				{
					$text = file_get_contents($text);
				}
				else
				{
					$text = trans('pages::pages.file not found') . ' (app/public/' . $path . ')';
				}

				$content = str_replace($match[0], $text, $content);
			}
		}

		return $next($content);
	}
}
