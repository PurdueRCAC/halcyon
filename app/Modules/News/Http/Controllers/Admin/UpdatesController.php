<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;
use App\Modules\News\Notifications\ArticleUpdated;
use App\Halcyon\Http\Concerns\UsesFilters;

class UpdatesController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of entries
	 *
	 * @param  int  $art  Article ID
	 * @param  Request $request
	 * @return View
	 */
	public function index($art, Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'news.updates', [
			'article'   => 0,
			'search'    => null,
			'state'     => 'published',
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'datetimecreated',
			'order_dir' => 'desc'
		]);

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
	 * @param   Request $request
	 * @param   int  $art  Article ID
	 * @return  View
	 */
	public function create(Request $request, $art)
	{
		$article = Article::findOrFail($art);

		$row = new Update();
		$row->newsid = $article->id;

		if ($fields = $request->old('fields'))
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
	 * @param   Request $request
	 * @param   int  $art  Article ID
	 * @param   int  $id   Update ID
	 * @return  View
	 */
	public function edit(Request $request, $art, $id)
	{
		$article = Article::findOrFail($art);

		$row = Update::withTrashed()->findOrFail($id);

		if ($fields = $request->old('fields'))
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
	 * @return  RedirectResponse
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

		$row = Update::findOrNew($id);
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
	 * @return  RedirectResponse
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
			$row = Update::find($id);

			if (!$row)
			{
				continue;
			}

			if (!$row->delete())
			{
				$request->session()->flash('error', trans('global.messages.delete failed'));
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
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		$article = app('request')->input('fields.newsid');
		$article = $article ?: app('request')->input('article');

		return redirect(route('admin.news.updates', ['article' => $article]));
	}
}
