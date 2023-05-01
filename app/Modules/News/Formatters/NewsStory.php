<?php
namespace App\Modules\News\Formatters;

use App\Modules\News\Models\Article;
use Closure;

/**
 * Adjust header levels for the page
 */
class NewsStory
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
		$text = preg_replace_callback("/(news)\s*(story|item)?\s*#?(\d+)(\{.+?\})?/i", array($this, 'matchNews'), $data['content']);

		$data['content'] = $text;

		return $next($data);
	}

	/**
	 * Expand NEWS#123 to linked article titles
	 * This resturns the linked title in MarkDown syntax
	 *
	 * @param   array<int,string>  $match
	 * @return  string
	 */
	private function matchNews(array $match): string
	{
		$title = trans('news::news.news story number', ['number' => $match[3]]);

		$news = Article::find($match[3]);

		if (!$news)
		{
			return $match[0];
		}

		$title = $news->headline;

		if (isset($match[4]))
		{
			$title = preg_replace("/[{}]+/", '', $match[4]);
		}

		return '[' . $title . '](' . route('site.news.show', ['id' => $match[3]]) . ')';
	}
}
