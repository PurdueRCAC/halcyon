<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Widgets\News;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;

/**
 * Display news articles from selected category
 */
class News extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$query = Article::query()
			->wherePublished();

		$type = new Type();

		if ($id = (int)$this->params->get('catid'))
		{
			$type = Type::findOrFail($id);

			$query->where('newstypeid', '=', $id);
		}

		if ($location = (string)$this->params->get('location'))
		{
			$query->where('location', '=', $location);
		}

		$articles = $query
			->orderBy('datetimenews', 'desc')
			->limit($this->params->get('limit', 5))
			->get();

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'articles' => $articles,
			'params'   => $this->params,
			'type'     => $type
		]);
	}
}
