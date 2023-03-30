<?php
namespace App\Widgets\News;

use App\Modules\Widgets\Entities\Widget;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Modules\News\Models\Newsresource;
use Carbon\Carbon;

/**
 * Display news articles from selected category
 */
class News extends Widget
{
	/**
	 * Display
	 *
	 * @return  null|\Illuminate\View\View
	 */
	public function run()
	{
		$a = (new Article)->getTable();

		$query = Article::query()
			->select($a . '.*')
			->wherePublished();

		$type = new Type();

		if ($id = $this->params->get('catid'))
		{
			$type = Type::findOrFail((int)$id);

			$query->where($a . '.newstypeid', '=', $id);
		}

		if ($location = $this->params->get('location'))
		{
			$query->where($a . '.location', '=', (string)$location);
		}

		if ($resources = $this->params->get('resources'))
		{
			$r = (new Newsresource)->getTable();

			$query->join($r, $r . '.newsid', $a . '.id');
			$query->whereIn($r . '.resourceid', $resources);
		}
	
		$now = Carbon::now()->toDateTimeString();
		$query->where(function($where) use ($now, $a)
		{
			$where->whereNull($a . '.datetimenewsend')
				->orWhere($a . '.datetimenewsend', '>', $now);
		});

		$query->orderBy($a . '.datetimenews', 'desc');

		$limit = $this->params->get('limit', 5);

		if ($limit > 0)
		{
			$articles = $query->limit($limit)->get();
		}
		else
		{
			$articles = $query
				->paginate(20, ['*'], 'page', request()->input('page', 1));
		}

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		$this->params->set('show_title', $this->model->showtitle);
		$this->params->set('title', $this->model->title);

		return view($this->getViewName($layout), [
			'articles' => $articles,
			'params'   => $this->params,
			'type'     => $type,
			'limit'    => $limit,
		]);
	}
}
