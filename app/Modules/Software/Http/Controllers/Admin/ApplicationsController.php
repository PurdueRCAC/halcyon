<?php

namespace App\Modules\Software\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use App\Modules\Software\Models\Type;
use App\Modules\Software\Models\Application;
use App\Modules\Software\Models\Version;
use App\Modules\Software\Models\VersionResource;
use App\Modules\Resources\Models\Asset;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class ApplicationsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return View
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'state'    => 'published',
			'type'     => null,
			'resource' => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Application::$orderBy,
			'order_dir' => Application::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('software.admin.filter_' . $key)
			 && $request->input($key) != session()->get('software.admin.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('software.admin.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'title', 'state', 'published_at']))
		{
			$filters['order'] = Application::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Application::$orderDir;
		}

		if (!auth()->user() || !auth()->user()->can('manage software'))
		{
			$filters['state'] = 'published';
		}

		$types = $this->getTypes();

		$resources = $this->getResources();

		// Get records
		$a = (new Application)->getTable();

		$query = Application::query()
			->select($a . '.*');

		if ($filters['state'] == 'published')
		{
			$query->where($a . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($a . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		if ($filters['type'] && $filters['type'] != '*')
		{
			$query->where($a . '.type_id', '=', $filters['type']);
		}

		if ($filters['resource'] && $filters['resource'] != '*')
		{
			$v = (new Version)->getTable();
			$r = (new VersionResource)->getTable();

			$query->join($v, $v . '.application_id', $a . '.id')
				->join($r, $r . '.version_id', $v . '.id')
				->where($r . '.resource_id', '=', $filters['resource'])
				->groupBy($a . '.id');
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('software::admin.applications.index', [
			'rows' => $rows,
			'filters' => $filters,
			'types' => $types,
			'resources' => $resources,
		]);
	}

	/**
	 * Get the list of types
	 *
	 * @return Collection
	 */
	private function getTypes()
	{
		return Type::query()
			->withCount('applications')
			->orderby('title', 'asc')
			->get();
	}

	/**
	 * Get the list of resources
	 *
	 * @return Collection
	 */
	private function getResources()
	{
		$query = Asset::query()
			->where('display', '>', 0)
			->where(function($where)
			{
				$where->whereNotNull('listname')
					->where('listname', '!=', '');
			})
			->where('resourcetype', '!=', 0);

		$limit = config('module.software.resource_type', []);

		if (!empty($limit))
		{
			$query->whereIn('resourcetype', $limit);
		}

		return $query
			->orderBy('name', 'asc')
			->get();
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Application();
		$row->state = 1;
		$row->access = 1;

		if ($fields = $request->old())
		{
			$row->fill($fields);
		}

		$types = $this->getTypes();

		$resources = $this->getResources();

		return view('software::admin.applications.edit', [
			'row' => $row,
			'types' => $types,
			'resources' => $resources,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View
	 */
	public function edit(Request $request, $id)
	{
		$row = Application::findOrFail($id);

		if ($fields = $request->old())
		{
			$row->fill($fields);
		}

		$types = $this->getTypes();

		$resources = $this->getResources();

		return view('software::admin.applications.edit', [
			'row' => $row,
			'types' => $types,
			'resources' => $resources,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'type_id' => 'nullable|integer|min:1',
			'title' => 'required|string|max:255',
			'alias' => 'nullable|string|max:255',
			'description' => 'nullable|string|max:500',
			'content' => 'nullable|string',
			'state' => 'nullable|integer',
			'access' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Application::findOrNew($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->$key = $request->input($key);
			}
		}
		if (!$row->alias)
		{
			$row->alias = $row->title;
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		if ($request->has('version'))
		{
			$versions = $request->input('version');

			$prev = $row->versions;
			$current = array();

			foreach ($versions as $i => $version)
			{
				if (!$version['title'])
				{
					continue;
				}

				$v = Version::findOrNew($version['id']);
				$v->title = $version['title'];
				$v->application_id = $row->id;
				$v->save();
				if (isset($version['resources']))
				{
					$v->setResources($version['resources']);
				}

				$current[] = $v->id;
			}

			foreach ($prev as $v)
			{
				if (!in_array($v->id, $current))
				{
					$v->delete();
				}
			}
		}

		return $this->cancel()->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
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
			$row = Application::find($id);

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

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.software.index'));
	}
}
