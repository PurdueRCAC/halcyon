<?php
namespace App\Modules\Knowledge\Formatters;

use Closure;

/**
 * Adjust header levels for the page
 */
class PermalinkHeaders
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
		$headers = array();
		$toc = array();

		$text = preg_replace_callback(
			'/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i',
			function($matches) use (&$headers, &$toc, $data)
			{
				$attr = trans('knowledge::knowledge.link to section', [
					'section'  => e(strip_tags($matches[3])),
					'headline' => e(strip_tags($data['headline']))
				]);

				if (!stripos($matches[0], 'id='))
				{
					$title = $matches[3];
					$title = preg_replace('/<.*?>/', '', $title);
					$title = trim($title);
					$title = strtolower($title);
					$title = str_replace(' ', '_', $title);
					$title = preg_replace('/[^a-z0-9\-_]+/', '', $title);
					$title = (request('all') ? $data['id'] . '-' : '') . $title;

					if (!isset($headers[$title]))
					{
						$headers[$title] = 0;
					}
					$headers[$title]++;

					if ($headers[$title] > 1)
					{
						$title = $headers[$title] . '_' . $title;
					}
					$toc[$title] = $matches[3];

					$anchor = '<a href="#' . $title . '" class="heading-anchor" title="' . $attr . '"><span class="fa fa-link" aria-hidden="true"></span><span class="sr-only">' . $attr . '</span></a> ';

					$matches[0] = $matches[1] . ' id="' . $title . '">' . $anchor . $matches[3] . $matches[4];
				}
				else
				{
					$matches[0] = preg_match('/id="(.*?)"/i', $matches[0], $matcs);

					$title = $matcs[1];

					$anchor = '<a href="#' . $title . '" class="heading-anchor" title="' . $attr . '"><span class="fa fa-link" aria-hidden="true"></span><span class="sr-only">' . $attr . '</span></a> ';

					$matches[0] = $matches[1] . '>' . $anchor . $matches[3] . $matches[4];
				}
				return $matches[0];
			},
			$data['content']
		);

		/*if (isset($data['params']['show_page_toc']) && $data['params']['show_page_toc'])
		{
			$t  = '<div class="table-of-contents">';
			$t .= '<ul>';
			foreach ($toc as $anchor => $header)
			{
				$t .= '<li><a href="#' . $anchor . '">' . $header. '</a></li>';
			}
			$t .= '</ul>';
			$t .= '</div>';

			$text = $t . $text;
		}*/

		$data['content'] = $text;

		return $next($data);
	}
}
