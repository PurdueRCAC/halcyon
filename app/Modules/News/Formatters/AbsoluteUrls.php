<?php
namespace App\Modules\News\Formatters;

use Closure;

/**
 * Make paths absolute
 */
class AbsoluteUrls
{
	/**
	 * Handle content
	 *
	 * @param  array $data
	 * @param  Closure $next
	 * @return array
	 */
	public function handle(array $data, Closure $next): array
	{
		// Auto-expand relative URLs to absolute
		$data['content'] = preg_replace_callback(
			'/\[.*?\]\(([^\)]+)\)/i',
			function ($matches)
			{
				if (substr($matches[1], 0, 4) == 'http')
				{
					return $matches[0];
				}

				return str_replace($matches[1], asset(ltrim($matches[1], '/')), $matches[0]);
			},
			$data['content']
		);

		return $next($data);
	}
}
