<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Update;
use App\Halcyon\Http\StatefulRequest;

class UpdatesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
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

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('news.updates.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$article = Article::findOrFail($art);

		$query = $article->updates();

		if ($filters['search'])
		{
			$query->where('body', 'like', '%' . $filters['search'] . '%');
		}

		if ($filters['state'] == 'published')
		{
			$query->where('datetimeremoved', '=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->where('datetimeremoved', '!=', '0000-00-00 00:00:00');
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
	 * @return  Response
	 */
	public function create($art)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$article = Article::findOrFail($art);

		$row = new Update();
		$row->newsid = $article->id;

		return view('news::admin.updates.edit', [
			'row'     => $row,
			'article' => $article
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($art, $id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$article = Article::findOrFail($art);

		$row = Update::findOrFail($id);

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
		$request->validate([
			'fields.body'   => 'required',
			'fields.newsid' => 'required'
		]);

		$id = $request->input('id');

		if ($id)
		{
			$row = Update::findOrFail($id);
			$row->fill($request->input('fields'));
		}
		else
		{
			$row = new Update($request->input('fields'));
		}

		/*if ($request->input('published') == 'trashed' && !$row->isDeleted())
		{
			$row->datetimeremoved = Carbon\Carbon::now()->toDateTimeString();
		}*/

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return redirect(route('admin.news.updates', ['article' => $row->newsid]))->withSuccess('Item created!');
	}

	/**
	 * Remove the specified entry
	 *
	 * @return  Response
	 */
	public function destroy()
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
			$request->session()->flash('success', trans('messages.item deleted', $success));
		}

		return $this->cancel();
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
