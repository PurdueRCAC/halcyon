<?php

namespace App\Modules\Pages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\Version;
use App\Halcyon\Http\Concerns\UsesFilters;
use Carbon\Carbon;

class PagesController extends Controller
{
	use UsesFilters;

	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return View
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = $this->getStatefulFilters($request, 'pages', [
			'search'    => null,
			'state'     => 'published',
			'access'    => 0,
			'parent'    => 0,
			// Paging
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			// Sorting
			'order'     => 'path',
			'order_dir' => 'asc',
		]);

		if (!in_array($filters['order'], ['id', 'title', 'path', 'lft', 'rgt', 'level', 'state', 'access', 'created_at', 'updated_at']))
		{
			$filters['order'] = 'lft';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		if (!auth()->user() || !auth()->user()->can('edit.state pages'))
		{
			$filters['state'] = 'published';
		}

		// Get records
		$query = Page::query()
			->select([
				'id', 'title', 'alias', 'state',
				'access', 'created_at', 'updated_at',
				'parent_id', 'level', 'path'
			])
			->with('viewlevel')
			->whereState($filters['state'])
			->whereAccess($filters['access'], auth()->user());

		if ($filters['search'])
		{
			$query->whereSearch($filters['search']);
		}

		if ($filters['parent'])
		{
			$query->whereParent($filters['parent']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('pages::admin.index', [
			'rows'    => $rows,
			'filters' => $filters
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @param  Request $request
	 * @return View
	 */
	public function create(Request $request)
	{
		$row = new Page;
		$row->access = 1;
		$row->state = 1;
		$row->created_at = Carbon::now();

		foreach (['show_title', 'show_author', 'show_create_date', 'show_modify_date', 'show_publish_date', 'layout'] as $key)
		{
			$val = config('module.pages.' . $key);

			if (!is_null($val))
			{
				$row->params->set($key, $val);
			}
		}

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$parents = Page::query()
			->select('id', 'title', 'path', 'level', 'access')
			->where('level', '>', 0)
			->orderBy('lft', 'asc')
			->get();

		return view('pages::admin.edit', [
			'row'     => $row,
			'parents' => $parents
		]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  Request $request
	 * @param  int  $id
	 * @return View
	 */
	public function edit(Request $request, $id)
	{
		$row = Page::findOrFail($id);

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		// Fail if checked out not by 'me'
		if ($row->checked_out
		 && $row->checked_out <> auth()->user()->id)
		{
			return redirect(route('admin.pages.index'))->with('warning', trans('global.checked out'));
		}

		$parents = Page::query()
			->select('id', 'title', 'path', 'level', 'access')
			->where('level', '>', 0)
			->orderBy('path', 'asc')
			->get();

		return view('pages::admin.edit', [
			'row'     => $row,
			'parents' => $parents
		]);
	}

	/**
	 * Show history for the page
	 * 
	 * @param  int  $id
	 * @return View
	 */
	public function history(int $id)
	{
		$row = Page::findOrFail($id);

		$history = $row->history()
			->orderBy('created_at', 'desc')
			->get();

		return view('pages::admin.history', [
			'row' => $row,
			'history' => $history,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function store(Request $request)
	{
		$rules = [
			'fields.parent_id' => 'nullable|integer',
			'fields.title' => 'required|string|max:255',
			'fields.alias' => 'nullable|string|max:255',
			'fields.content' => 'required|string',
			'fields.access' => 'nullable|min:1',
			'fields.state' => 'nullable|int',
			'fields.publish_up' => 'nullable|date',
			'fields.publish_down' => 'nullable|date',
			'fields.metakey' => 'nullable|string',
			'fields.metadesc' => 'nullable|string',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('id');

		$row = Page::findOrNew($id);
		$row->fill($request->input('fields'));

		//$row->metakey = $request->input('tags');

		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				$params[$key] = is_array($val) ? array_filter($val) : $val;
			}

			$row->params = new Repository($params);
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		$tags = explode(',', $row->metakey);
		$tags = array_map('trim', $tags);
		$row->setTags($tags);

		// Rebuild the set
		$root = Page::rootNode();
		$row->rebuild($root->id);

		return redirect(route('admin.pages.index'))->withSuccess(trans('global.messages.item ' . ($id ? 'created' : 'updated')));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param   Request $request
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
			$row = Page::find($id);

			if (!$row)
			{
				continue;
			}

			if ($row->trashed())
			{
				if (!$row->forceDelete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
			}
			elseif (!$row->delete())
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

		return redirect(route('admin.pages.index'));
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function state(Request $request, int $id = null)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'pages::pages.select to publish' : 'pages::pages.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Page::findOrFail(intval($id));

			if ($row->state == $state)
			{
				continue;
			}

			$row->timestamps = false;
			$row->state = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'pages::pages.items published'
				: 'pages::pages.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function restore(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans('pages::pages.select to restore'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Page::withTrashed()->findOrFail(intval($id));

			if (!$row->restore())
			{
				$request->session()->flash('error', trans('global.messages.restore failed'));
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$request->session()->flash('success', trans('pages::pages.items restored', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Revert to prior version
	 * 
	 * @param  int  $id
	 * @param  int  $revision
	 * @return RedirectResponse
	 */
	public function revert(int $id, int $revision)
	{
		$row = Page::findOrFail($id);

		$history = $row->history()
			->orderBy('created_at', 'desc')
			->get();

		foreach ($history as $action)
		{
			if ($action->id == $revision)
			{
				break;
			}

			$changes = json_decode(json_encode($action->old), true);

			foreach ($changes as $key => $val)
			{
				if ($key == 'created_at' || $key == 'updated_at' || $key == 'deleted_at')
				{
					continue;
				}
				$row->{$key} = $val;
			}

			$action->delete();
		}
		$row->saveQuietly();

		return redirect(route('admin.pages.history', [$id]));
	}

	/**
	 * Reorder entries
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder(Request $request)
	{
		// Incoming
		$id = $request->input('id', array());
		if (is_array($id))
		{
			$id = (!empty($id) ? $id[0] : 0);
		}

		// Ensure we have an ID to work with
		if (!$id)
		{
			$request->session()->flash('warning', trans('pages::pages.error.no id'));
			return $this->cancel();
		}

		// Get the element being moved
		$model = Page::findOrFail($id);

		$move = ($request->input('action') == 'orderup') ? -1 : +1;

		if (!$model->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Rebuild the tree
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function rebuild(Request $request)
	{
		// Get the root of the tree
		$model = Page::rootNode();

		if (!$model->rebuild($model->id))
		{
			$request->session()->flash('error', trans('pages::pages.rebuild failed'));
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Copy an entry and all associated data
	 *
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function copy(Request $request)
	{
		// Article to copy
		$from = $request->input('from_id', 0);
		// Parent to copy article to
		$to   = $request->input('to_id', 0);
		// Copy descendents as well?
		$recursive = $request->input('recursive', 0);

		if (!$from || !$to)
		{
			$request->session()->flash('warning', trans('pages::pages.error.no id'));
			return $this->cancel();
		}

		$from = Page::findOrFail($from);
		$to   = Page::findOrFail($to);

		if (!$from->id)
		{
			$request->session()->flash('warning', trans('pages::pages.error.no id'));
			return $this->cancel();
		}

		// Copy article
		if (!$from->duplicate($to->id, $recursive))
		{
			$request->session()->flash('error', trans('pages::pages.error.copy failed'));
			return $this->cancel();
		}

		// Redirect back to the courses page
		$request->session()->flash('success', trans('pages::pages.item copied'));

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  RedirectResponse
	 */
	public function cancel()
	{
		return redirect(route('admin.pages.index'));
	}
}
