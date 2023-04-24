<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Make file paths absolute
 */
class AbsoluteFilePaths
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
		$text = preg_replace_callback(
			'/\ssrc="(.*?)"/i',
			function ($matches)
			{
				if (substr($matches[1], 0, 4) == 'http')
				{
					return ' src="' . $matches[1] . '"';
				}

				if (substr($matches[1], 0, 7) == '/files/')
				{
					$matches[1] = substr($matches[1], 7);
				}

				if (substr($matches[1], 0, 6) == 'files/')
				{
					$matches[1] = substr($matches[1], 6);
				}

				return ' src="' . asset("files/" . ltrim($matches[1], '/')) . '"';
			},
			$data['content']
		);
		//$text = preg_replace('/src="(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/src="\/include\/images\/(.*?)"/i', 'src="' . asset("files/$1") . '"', $text);
		$text = preg_replace('/href="\/(.*?)"/i', 'href="' . url("$1") . '"', $text);

		$data['content'] = $text;

		return $next($data);
	}
}
