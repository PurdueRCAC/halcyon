<?php
namespace App\Modules\Issues\Formatters;

use Closure;

/**
 * Highlight Unused Variables in content
 */
class HighlightUnusedVariables
{
	/**
	 * Handle content
	 *
	 * @param  array<string,string> $data
	 * @param  Closure $next
	 * @return array
	 */
	public function handle(array $data, Closure $next): array
	{
		$text = $data['content'];

		// Highlight unused variables for admins
		if (auth()->user() && auth()->user()->can('manage issues'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		if (auth()->user() && auth()->user()->can('manage issues'))
		{
			$text = preg_replace("/%([\w]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$data['content'] = $text;

		return $next($data);
	}
}
