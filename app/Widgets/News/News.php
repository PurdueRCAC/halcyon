<?php
namespace App\Widgets\News;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Newsresource;

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
		$a = (new Article)->getTable();

		$query = Article::query()
			->select($a . '.*')
			->wherePublished();

		$type = new Type();

		if ($id = (int)$this->params->get('catid'))
		{
			$type = Type::findOrFail($id);

			$query->where($a . '.newstypeid', '=', $id);
		}

		if ($location = (string)$this->params->get('location'))
		{
			$query->where($a . '.location', '=', $location);
		}

		if ($resources = $this->params->get('resources'))
		{
			$r = (new Newsresource)->getTable();

			$query->join($r, $r . '.newsid', $a . '.id');
			$query->whereIn($r . '.resourceid', $resources);
		}

		$articles = $query
			->orderBy($a . '.datetimenews', 'desc')
			->limit($this->params->get('limit', 5))
			->get();

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		$this->params->set('show_title', $this->model->showtitle);
		$this->params->set('title', $this->model->title);

		return view($this->getViewName($layout), [
			'articles' => $articles,
			'params'   => $this->params,
			'type'     => $type
		]);
	}
}
