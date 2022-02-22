<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;
use App\Modules\News\Notifications\ArticleUpdated;
use App\Halcyon\Http\StatefulRequest;

class UpdatesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @param  integer  $art  Article ID
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index($art, StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'article'   => 0,
			'search'    => null,
			'state'     => 'published',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'datetimecreated',
			'order_dir' => 'desc'
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) && session()->has('news.updates.filter_' . $key)
			 && $request->input($key) != session()->get('news.updates.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('news.updates.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$article = Article::findOrFail($art);

		$query = Update::query()
			->withTrashed()
			->where('newsid', $article->id);

		if ($filters['search'])
		{
			$query->where('body', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['state'] == 'published')
		{
			$query->whereNull('datetimeremoved');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->whereNotNull('datetimeremoved');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('news::admin.updates.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'article' => $article
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   integer  $art  Article ID
	 * @return  Response
	 */
	public function create($art)
	{
		$article = Article::findOrFail($art);

		$row = new Update();
		$row->newsid = $article->id;

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('news::admin.updates.edit', [
			'row'     => $row,
			'article' => $article
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $art  Article ID
	 * @param   integer  $id   Update ID
	 * @return  Response
	 */
	public function edit($art, $id)
	{
		$article = Article::findOrFail($art);

		$row = Update::withTrashed()->findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		return view('news::admin.updates.edit', [
			'row'     => $row,
			'article' => $article
		]);
	}

	/**
	 * Store an entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'fields.body'   => 'required|string|max:15000',
			'fields.newsid' => 'required|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = $id ? Update::findOrFail($id) : new Update;
		$row->fill($request->input('fields'));

		if (!$id)
		{
			$row->userid = auth()->user()->id;
		}
		else
		{
			$row->edituserid = auth()->user()->id;
		}

		if (!$row->save())
		{
			return redirect()->back()->with('error', trans('global.messages.save failed'));
		}

		if ($row->article->published && !$row->article->template && in_array($row->article->newstypeid, config('module.news.notify_update', [])))
		{
			$row->article->notify(new ArticleUpdated($row->article));
		}

		return redirect(route('admin.news.updates', ['article' => $row->newsid]))->withSuccess(trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Update::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		//return $this->cancel($request);
		return redirect(route('admin.news.updates', ['article' => $request->input('article')]));
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		$article = app('request')->input('fields.newsid');
		$article = $article ?: app('request')->input('article');

		return redirect(route('admin.news.updates', ['article' => $article]));
	}
}
