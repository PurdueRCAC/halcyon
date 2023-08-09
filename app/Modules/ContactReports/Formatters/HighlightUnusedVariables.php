<?php
namespace App\Modules\ContactReports\Formatters;

use Closure;

/**
 * Highlight Unused Variables in content
 */
class HighlightUnusedVariables
{
	/**
	 * Handle content
	 *
	 * @param  array<string,mixed> $data
	 * @param  Closure $next
	 * @return array<string,mixed>
	 */
	public function handle(array $data, Closure $next): array
	{
		$text = $data['content'];

		// Highlight unused variables for admins
		if (auth()->user() && auth()->user()->can('manage contactreports'))
		{
			$text = preg_replace("/%%([\w\s]+)%%/", '<span style="color:red">$0</span>', $text);
		}

		if (auth()->user() && auth()->user()->can('manage contactreports'))
		{
			$text = preg_replace("/%([\w]+)%/", '<span style="color:red">$0</span>', $text);
		}

		$data['content'] = $text;

		return $next($data);
	}
}
