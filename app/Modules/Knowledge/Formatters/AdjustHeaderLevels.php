<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Adjust header levels for the page
 */
class AdjustHeaderLevels
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
		$text = preg_replace("/<p>(.*)<\/p>\n<(table.*)\n/m", "<$2 <caption>$1</caption>\n", $data['content']);
		$text = preg_replace("/<h2>(.*)<\/h2>/", "<h3>$1</h3>", $text);
		$text = preg_replace("/<h1>(.*)<\/h1>/", "<h2>$1</h2>", $text);

		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		$data['content'] = $text;

		return $next($data);
	}
}
