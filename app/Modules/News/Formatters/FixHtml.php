<?php
namespace App\Modules\News\Formatters;

use Closure;

/**
 * Fix some HTML for accessibility and legacy
 */
class FixHtml
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

		$text = str_replace('<th>', '<th scope="col">', $text);
		$text = str_replace('align="right"', 'class="text-right text-end"', $text);

		$text = preg_replace('/<p>([^\n]+)<\/p>\n(<table.*?>)(.*?<\/table>)/usm', '$2 <caption>$1</caption>$3', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);

		$data['content'] = $text;

		return $next($data);
	}
}
