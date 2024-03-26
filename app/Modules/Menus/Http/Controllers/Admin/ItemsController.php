<?php

namespace App\Modules\Menus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menus\Helpers\ItemType;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use App\Halcyon\Html\Builder\Select;
use App\Halcyon\Models\Extension;
use App\Halcyon\Http\StatefulRequest;
use Carbon\Carbon;

class ItemsController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @param  string  $menutype
	 * @return View|RedirectResponse
	 */
	public function index(StatefulRequest $request, $menutype = null)
	{
		// Get filters
		$filters = array(
			'menutype' => $menutype,
			'search'   => null,
			'state'    => 'published',
			'access'   => null,
			'parent'   => 0,
			'level'    => 0,
			'language' => '',
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => Item::$orderBy,
			'order_dir' => Item::$orderDir,
		);

		$reset = false;
		$request = $request->mergeWithBase();
		foreach ($filters as $key => $default)
		{
			if ($key == 'menutype' && $filters['menutype'])
			{
				continue;
			}
			if ($key != 'page'
			 && $request->has($key) //&& session()->has('menus.items.filter_' . $key)
			 && $request->input($key) != session()->get('menus.items.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('menus.items.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'title', 'published', 'access']))
		{
			$filters['order'] = Item::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Item::$orderDir;
		}

		// Get records
		$menu = Type::findByMenutype((string)$filters['menutype']);

		if (!$menu)
		{
			return redirect(route('admin.menus.index'));
		}

		$query = Item::query();

		$a = (new Item)->getTable();

		// Select all fields from the table.
		$query->select([
			$a . '.id',
			$a . '.menutype',
			$a . '.title',
			$a . '.alias',
			$a . '.path',
			$a . '.link',
			$a . '.type',
			$a . '.parent_id',
			$a . '.level',
			$a . '.published',
			$a . '.module_id',
			$a . '.ordering',
			$a . '.checked_out',
			$a . '.checked_out_time',
			$a . '.access',
			$a . '.lft',
			$a . '.rgt',
			$a . '.home',
			$a . '.language',
			$a . '.client_id',
			$a . '.deleted_at',
		]);

		// Exclude the root category.
		$query->where($a . '.id', '>', 1);
		$query->where($a . '.client_id', '=', 0);

		// Filter on the published state.
		if ($filters['state'] == 'published')
		{
			$query->where($a . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($a . '.published', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		// Filter by search in title, alias or id
		if ($filters['search'])
		{
			$search = trim($filters['search']);
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id', '=', (int) substr($search, 3));
			}
			elseif (stripos($search, 'link:') === 0)
			{
				if ($search = substr($search, 5))
				{
					$query->where($a . '.link', 'like', '%' . $search . '%');
				}
			}
			else
			{
				$query->where(function($where) use ($a, $search)
				{
					$where->where($a . '.title', 'like', '%' . $search . '%')
						->orWhere($a . '.alias', 'like', '%' . $search . '%')
						->orWhere($a . '.content', 'like', '%' . $search . '%');
				});
			}
		}

		// Filter the items over the parent id if set.
		$parentId = $filters['parent'];
		if (!empty($parentId))
		{
			$query->where($a . '.parent_id', '=', (int)$parentId);
		}

		// Filter the items over the menu id if set.
		$menuType = $filters['menutype'];
		if (!empty($menuType))
		{
			$query->where($a . '.menutype', '=', $menuType);
		}

		// Filter on the access level.
		if ($access = $filters['access'])
		{
			$query->where($a . '.access', '=', (int) $access);
		}

		// Implement View Level Access
		if (!auth()->user()->can('admin'))
		{
			$query->whereIn($a . '.access', auth()->user()->getAuthorisedViewLevels());
		}

		// Filter on the level.
		if ($level = $filters['level'])
		{
			$query->where($a . '.level', '<=', (int) $level);
		}

		// Filter on the language.
		if ($language = $filters['language'])
		{
			$query->where($a . '.language', '=', $language);
		}

		// Get records
		$rows = $query
			->with('viewlevel')
			->orderBy($filters['order'], $filters['order_dir'])
			->get();

		$menus = Type::query()
			->orderBy('title', 'asc')
			->get();

		return view('menus::admin.items.index', [
			'rows'     => $rows,
			'filters'  => $filters,
			'menu'     => $menu,
			'menus'    => $menus,
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  View
	 */
	public function create(Request $request)
	{
		$row = new Item;
		$row->type = 'module';
		$row->menutype = $request->input('menutype');

		if (!$row->menutype)
		{
			$row->menutype = $request->session()->get('menus.items.menutype', $row->menutype);
		}

		switch ($row->type)
		{
			case 'separator':
			case 'html':
				$row->link = '';
				$row->module_id = 0;
				break;

			case 'url':
				$row->module_id = 0;
				break;

			case 'module':
			default:
				break;
		}

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		$form = $row->getForm();

		$widgets = \App\Modules\Menus\Models\Widget::forMenuId($row->id ? $row->id : 0);

		return view('menus::admin.items.edit', [
			'row' => $row,
			'form' => $form,
			'widgets' => $widgets,
		]);
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   Request $request
	 * @param   int  $id
	 * @return  View|RedirectResponse
	 */
	public function edit(Request $request, $id)
	{
		$row = Item::withTrashed()->findOrFail($id);

		// Fail if checked out not by 'me'
		if ($row->isCheckedOut())
		{
			return $this->cancel()->with('warning', trans('global.messages.item checked out'));
		}

		if ($fields = $request->old('fields'))
		{
			$row->fill($fields);
		}

		switch ($row->type)
		{
			case 'alias':
				$row->module_id = 0;
				$args = array();

				parse_str(parse_url($row->link, PHP_URL_QUERY), $args);
				break;

			case 'separator':
			case 'html':
				$row->link = '';
				$row->module_id = 0;
				break;

			case 'url':
				$row->module_id = 0;

				//parse_str(parse_url($row->link, PHP_URL_QUERY));
				break;

			case 'module':
			default:
				// Enforce a valid type.
				$row->type = 'module';

				// Ensure the integrity of the module_id field is maintained, particularly when changing the menu item type.
				//$args = array();
				//parse_str(parse_url($row->link, PHP_URL_QUERY), $args);
				$args = explode('.', $row->link);

				if (isset($args[1]))
				{
					// Load the language file for the module.
					$module = Extension::findByModule($args[1]);
					if ($module)
					{
						$module->registerLanguage();

						// Determine the module id.
						if ($module->id)
						{
							$row->module_id = $module->id;
						}
					}
				}
				break;
		}

		$form = $row->getForm();

		$widgets = \App\Modules\Menus\Models\Widget::forMenuId($row->id);

		return view('menus::admin.items.edit', [
			'row' => $row,
			'form' => $form,
			'widgets' => $widgets,
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
			'fields.menutype' => 'required|string|max:24',
			'fields.title' => 'nullable|string|max:255',
			'fields.content' => 'nullable|string',
			'fields.path' => 'nullable|string|max:1024',
			'fields.link' => 'nullable|string|max:1024',
			'params' => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('fields.id');

		$row = Item::findOrNew($id);
		$row->fill($request->input('fields'));
		$row->link = $row->link ? $row->link : '/';
		if ($request->has('fields.page_id'))
		{
			$row->page_id = $request->input('fields.page_id');
		}
		if ($request->has('params'))
		{
			$params = $request->input('params', []);

			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			return redirect()->back()->withError(trans('global.messages.save failed'));
		}

		// Set this to redirects work correctly.
		$request->merge(['menutype' => $row->menutype]);

		return $this->cancel($row->menutype)->with('success', trans('global.messages.item ' . ($id ? 'updated' : 'created')));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @param   int  $id
	 * @return  RedirectResponse
	 */
	public function delete(Request $request, $id = null)
	{
		// Incoming
		$ids = $request->input('id', $id);
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Note: This is recursive and will also remove all descendents
			$row = Item::withTrashed()->find($id);

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
			else
			{
				if (!$row->delete())
				{
					$request->session()->flash('error', trans('global.messages.delete failed'));
					continue;
				}
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
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @param   int $id
	 * @return  RedirectResponse
	 */
	public function state(Request $request, $id = null)
	{
		$action = app('request')->segment(count($request->segments()) - 1);
		$state  = $action == 'publish' ? 1 : 0;

		// Incoming
		$ids = $request->input('id', array($id));
		$ids = (!is_array($ids) ? array($ids) : $ids);

		// Check for an ID
		if (count($ids) < 1)
		{
			$request->session()->flash('warning', trans($state ? 'menus::menus.select to publish' : 'menus::menus.select to unpublish'));
			return $this->cancel();
		}

		$success = 0;
		$menutype = null;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Item::findOrFail(intval($id));
			$row->timestamps = false;

			if ($row->published == $state)
			{
				continue;
			}

			$row->published = $state;

			if (!$row->save())
			{
				$request->session()->flash('error', trans('global.messages.save failed'));
				continue;
			}

			$success++;
			$menutype = $row->menutype;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'global.messages.item published'
				: 'global.messages.item unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel($menutype);
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
			$request->session()->flash('warning', trans('menus::menus.select to restore'));
			return $this->cancel();
		}

		$success = 0;

		// Update record(s)
		foreach ($ids as $id)
		{
			$row = Item::withTrashed()->findOrFail(intval($id));

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
			$request->session()->flash('success', trans('menus::menus.items restored', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Reorder entries
	 * 
	 * @param   int  $id
	 * @param   Request $request
	 * @return  RedirectResponse
	 */
	public function reorder($id, Request $request)
	{
		$row = Item::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', trans('global.messages.move failed'));
		}

		return $this->cancel($row->menutype);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @param   Request  $request
	 * @return  RedirectResponse
	 */
	public function saveorder(Request $request)
	{
		// Get the input
		$order = $request->input('order', []);
		$ret = true;

		$item = null;

		foreach ($order as $i => $it)
		{
			list($parent_id, $id) = explode(':', $it);

			$item = Item::find($id);
			if (!$item)
			{
				continue;
			}
			$item->parent_id = intval($parent_id);
			$item->ordering = $i;
			if (!$item->save())
			{
				$ret = false;
			}
		}

		$item->rebuild(1, 0, 0, '', 'ordering');

		if ($ret === false)
		{
			// Reorder failed
			$request->session()->flash('success', trans('global.error.reorder failed'));
		}
		else
		{
			// Reorder succeeded.
			$request->session()->flash('success', trans('global.messages.ordering saved'));
		}

		// Redirect back to the listing
		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @param   string  $menutype
	 * @return  RedirectResponse
	 */
	public function cancel($menutype = null)
	{
		return redirect(route('admin.menus.items', [
			'menutype' => $menutype ? $menutype : request()->input('menutype', request()->input('fields.menutype'))
		]));
	}
}
