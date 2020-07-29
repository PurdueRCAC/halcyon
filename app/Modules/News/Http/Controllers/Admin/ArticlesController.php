<?php

namespace App\Modules\News\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use App\Halcyon\Http\StatefulRequest;

class ArticlesController extends Controller
{
	/**
	 * Display templates?
	 *
	 * @var  integer
	 */
	private $template = 0;

	/**
	 * Display a listing of articles
	 *
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'datetimecreated',
			'order_dir' => 'desc',
			'type'      => null,
		);

		$action = 'index';
		if ($this->template)
		{
			$action = 'template';
			$filters['state'] = '*';
		}

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('news.' . $action . '.filter_' . $key, $key, $default);
		}

		if (!in_array($filters['order'], ['id', 'headline', 'datetimecreated']))
		{
			$filters['order'] = 'datetimecreated';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$query = Article::query()
			->with('type')
			->where('template', '=', $this->template);

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters)
			{
				$query->where('headline', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'] == 'published')
		{
			$query->where('published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where('published', '=', 0);
		}

		if ($filters['type'])
		{
			$query->where('newstypeid', '=', $filters['type']);
		}

		$rows = $query
			->withCount('updates')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		$types = Type::query()
			->orderBy('name', 'asc')
			->get();

		return view('news::admin.articles.index', [
			'filters' => $filters,
			'rows'    => $rows,
			'types'   => $types,
			'template' => $this->template
		]);
	}

	/**
	 * Display a listing of templates
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function templates(StatefulRequest $request)
	{
		$this->template = 1;

		return $this->index($request);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @return  Response
	 */
	public function create()
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = new Article();

		$types = Type::orderBy('name', 'asc')->get();

		foreach ($types as $type)
		{
			if ($type->id == config('modules.news.default_type', 0))
			{
				$row->newstypeid = $type->id;
			}
		}

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function store(Request $request)
	{
		$request->validate([
			'fields.headline' => 'required',
			'fields.body' => 'required'
		]);

		$row = new Article($request->input('fields'));

		if (!$row->save())
		{
			return redirect()->back()->with('error', 'Failed to create item.');
		}

		return $this->cancel()->withSuccess('Item created!');
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		app('request')->merge(['hidemainmenu' => 1]);

		$row = Article::findOrFail($id);

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
		}

		$types = Type::orderBy('name', 'asc')->get();

		return view('news::admin.articles.edit', [
			'row'   => $row,
			'types' => $types
		]);
	}

	/**
	 * Update the specified entry
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'fields.headline' => 'required'
		]);

		$fields = $request->input('fields');
		$fields['location'] = (string)$fields['location'];

		$row = Article::findOrFail($id);
		$row->fill($fields);

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('messages.update failed'));
		}

		return $this->cancel()->withSuccess(trans('messages.update success'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @return  void
	 */
	public function state(Request $request, $id)
	{
		$action = $request->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'news::news.select to publish' : 'news::news.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Article::findOrFail(intval($id));

			if ($row->published == $state)
			{
				continue;
			}

			// Don't update last modified timestamp for state changes
			$row->timestamps = false;

			$row->published = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'news::news.items published'
				: 'news::news.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
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
			$row = Article::findOrFail($id);

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
		return redirect(route('admin.news.index'));
	}
}
