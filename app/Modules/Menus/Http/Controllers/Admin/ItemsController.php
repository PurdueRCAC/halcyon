<?php

namespace App\Modules\Menus\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
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
	 * @return Response
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

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->state('menus.items.' . $key, $key, $default);
		}

		if ($menutype)
		{
			$filters['menutype'] = $menutype;
		}

		if (!in_array($filters['order'], ['id', 'title', 'published', 'access']))
		{
			$filters['order'] = Item::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Item::$orderDir;
		}

		// Get records
		$menu = Type::findByMenutype($filters['menutype']);

		if (!$menu)
		{
			return redirect(route('admin.menus.index'));
		}

		$query = Item::query();

		$a = (new Item)->getTable();

		// Select all fields from the table.
		$query->select([$a . '.id',
			$a . '.menutype',
			$a . '.title',
			$a . '.alias',
			$a . '.note',
			$a . '.path',
			$a . '.link',
			$a . '.type',
			$a . '.parent_id',
			$a . '.level',
			$a . '.published AS state',
			$a . '.module_id',
			$a . '.ordering',
			$a . '.checked_out',
			$a . '.checked_out_time',
			$a . '.target',
			$a . '.access',
			$a . '.class',
			//$a . '.template_style_id',
			$a . '.params',
			$a . '.lft',
			$a . '.rgt',
			$a . '.home',
			$a . '.language',
			$a . '.client_id',
			$a . '.deleted_at',
			'l.title AS language_title',
			'l.image AS image',
			'u.name AS editor',
			'c.element AS componentname',
			'ag.title AS access_level',
			'e.name AS name',
			\DB::raw('CASE ' . $a . '.type' .
			' WHEN \'module\' THEN ' . $a . '.published+2*(e.enabled-1) ' .
			' WHEN \'url\' THEN ' . $a . '.published+2 ' .
			' WHEN \'alias\' THEN ' . $a . '.published+4 ' .
			' WHEN \'separator\' THEN ' . $a . '.published+6 ' .
			' END AS published')]);
		//$query->from($query->getTableName(), 'a');

		// Join over the language
		$query->leftJoin('languages AS l', 'l.lang_code', $a . '.language', 'left');

		// Join over the users.
		$query->leftJoin('users AS u', 'u.id', $a . '.checked_out');

		// Join over components
		$query->leftJoin('extensions AS c', 'c.id', $a . '.module_id');

		// Join over the asset groups.
		$query->leftJoin('viewlevels AS ag', 'ag.id', $a . '.access');

		// Join over the associations.
		/*$assoc = isset($app->menu_associations) ? $app->menu_associations : 0;
		if ($assoc)
		{
			$query->select('COUNT(asso2.id)>1 AS association');
			$query->leftJoin('associations AS asso', 'asso.id = ' . $a . '.id AND asso.context=\'com_menus.item\'');
			$query->leftJoin('associations AS asso2', 'asso2.key', 'asso.key');
			$query->groupBy($a . '.id');
		}*/

		// Join over the extensions
		$query->leftJoin('extensions AS e', 'e.id', $a . '.module_id');

		// Exclude the root category.
		$query->where($a . '.id', '>', 1);
		$query->where($a . '.client_id', '=', 0);

		// Filter on the published state.
		//$published = $filters['state'];
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
		/*else
		{
			$query->withTrashed();
		}
		if (is_numeric($published))
		{
			$query->withTrashed()->where($a . '.published', '=', (int) $published);
		}
		elseif ($published === '')
		{
			$query->withTrashed()->whereIn($a . '.published', array(0, 1));
		}
		elseif ($published == '*')
		{
			$query->withTrashed();
		}*/

		// Filter by search in title, alias or id
		if ($search = trim($filters['search']))
		{
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
						->orWhere($a . '.note', 'like', '%' . $search . '%');
				});
			}
		}

		// Filter the items over the parent id if set.
		$parentId = $filters['parent'];
		if (!empty($parentId))
		{
			$query->where('p.id', '=', (int)$parentId);
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
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		$ordering = array();

		// Preprocess the list of items to find ordering divisions.
		foreach ($rows as $item)
		{
			$ordering[$item->parent_id][] = $item->id;

			// item type text
			switch ($item->type)
			{
				case 'url':
					$value = trans('menus::menus.TYPE_EXTERNAL_URL');
					break;

				case 'alias':
					$value = trans('menus::menus.TYPE_ALIAS');
					break;

				case 'separator':
					$value = trans('menus::menus.TYPE_SEPARATOR');
					break;

				case 'module':
				default:
					// load language
					if (!empty($item->componentname))
					{
						$value = trans($item->componentname);
						$vars  = null;

						parse_str($item->link, $vars);

						if (isset($vars['view']))
						{
							// Attempt to load the view xml file.
							$file = app_path() . '/Modules/' . $item->componentname . '/Resources/views/site/' . $vars['view'] . '/metadata.xml';

							if (file_exists($file) && $xml = simplexml_load_file($file))
							{
								// Look for the first view node off of the root node.
								if ($view = $xml->xpath('view[1]'))
								{
									if (!empty($view[0]['title']))
									{
										$vars['layout'] = isset($vars['layout']) ? $vars['layout'] : 'default';

										// Attempt to load the layout xml file.
										// If Alternative Menu Item, get template folder for layout file
										if (strpos($vars['layout'], ':') > 0)
										{
											// Use template folder for layout file
											$temp = explode(':', $vars['layout']);
											$file = app_path() . '/Themes/' . $temp[0] . '/html/' . $item->componentname . '/' . $vars['view'] . '/' . $temp[1] . '.xml';
										}
										else
										{
											// Get XML file from component folder for standard layouts
											$file = app_path() . '/Modules/' . $item->componentname . '/Resources/views/site/' . $vars['view'] . '/' . $vars['layout'] . '.xml';
										}

										if (file_exists($file) && $xml = simplexml_load_file($file))
										{
											// Look for the first view node off of the root node.
											if ($layout = $xml->xpath('layout[1]'))
											{
												if (!empty($layout[0]['title']))
												{
													$value .= ' » ' . trans(trim((string) $layout[0]['title']));
												}
											}
											if (!empty($layout[0]->message[0]))
											{
												$item->item_type_desc = trans(trim((string) $layout[0]->message[0]));
											}
										}
									}
								}
								unset($xml);
							}
							else
							{
								// Special case for absent views
								$value .= ' » ' . trans($item->componentname . '::' . $item->componentname . '.' . $vars['view'] . '.VIEW_DEFAULT_TITLE');
							}
						}
					}
					else
					{
						if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result))
						{
							$value = trans('menus::menus.TYPE_UNEXISTING', ['type' => $result[1]]);
						}
						else
						{
							$value = trans('menus::menus.TYPE_UNKNOWN');
						}
					}
					break;
			}
			$item->item_type = $value;
		}

		// Levels filter.
		$options = array();
		$options[] = Select::option('1', 1);
		$options[] = Select::option('2', 2);
		$options[] = Select::option('3', 3);
		$options[] = Select::option('4', 4);
		$options[] = Select::option('5', 5);
		$options[] = Select::option('6', 6);
		$options[] = Select::option('7', 7);
		$options[] = Select::option('8', 8);
		$options[] = Select::option('9', 9);
		$options[] = Select::option('10', 10);

		return view('menus::admin.items.index', [
			'rows'    => $rows,
			'filters' => $filters,
			'menu'    => $menu,
			'f_levels' => $options,
			'ordering' => $ordering
		]);
	}

	/**
	 * Show the form for creating a new article
	 *
	 * @param   Request $request
	 * @return  Response
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

		if ($fields = app('request')->old('fields'))
		{
			$row->fill($fields);
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
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function edit($id)
	{
		$row = Item::withTrashed()->findOrFail($id);

		// Fail if checked out not by 'me'
		if ($row->isCheckedOut())
		{
			return $this->cancel()->with('warning', trans('global.messages.item checked out'));
		}

		if ($fields = app('request')->old('fields'))
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
	 * @return  Response
	 */
	public function store(Request $request)
	{
		//$request->validate([
		$rules = [
			'fields.menutype' => 'required|string|max:24',
			'fields.title' => 'nullable|string|max:255',
			'fields.path' => 'nullable|string|max:1024',
			'fields.link' => 'nullable|string|max:1024',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$id = $request->input('fields.id');

		$row = $id ? Item::findOrFail($id) : new Item();
		$row->fill($request->input('fields'));

		if (!$row->save())
		{
			$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

			return redirect()->back()->withError($error);
		}

		$root = Item::rootNode();
		$row->rebuild($root->id);

		// Set this to redirects work correctly.
		$request->merge(['menutype' => $row->menutype]);

		return $this->cancel()->with('success', trans('global.messages.item saved'));
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @param   integer  $id
	 * @return  Response
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
					$request->session()->flash('error', $row->getError());
					continue;
				}
			}
			else
			{
				if (!$row->delete())
				{
					$request->session()->flash('error', $row->getError());
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
	 * @param   integer $id
	 * @return  void
	 */
	public function state(Request $request, $id)
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
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		// Set message
		if ($success)
		{
			$msg = $state
				? 'menus::menus.items published'
				: 'menus::menus.items unpublished';

			$request->session()->flash('success', trans($msg, ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Sets the state of one or more entries
	 * 
	 * @param   Request $request
	 * @return  Response
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
				$request->session()->flash('error', $row->getError());
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
	 * @param   integer  $id
	 * @param   Request $request
	 * @return  Response
	 */
	public function reorder($id, Request $request)
	{
		// Incoming
		//$id = $request->input('id');

		// Get the element being moved
		$row = Item::findOrFail($id);
		$move = ($request->segment(4) == 'orderup') ? -1 : +1;

		if (!$row->move($move))
		{
			$request->session()->flash('error', $row->getError());
		}

		// Redirect
		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.menus.items', ['menutype' => request()->input('menutype', request()->input('fields.menutype'))]));
	}

	/**
	 * Temporary method. This should go into the 1.5 to 1.6 upgrade routines.
	 *
	 * @param   Request $request
	 * @return  Response
	 */
	public function types(Request $request)
	{
		$id = $request->input('recordId');

		$model = new ItemType();
		$types = $model->getTypeOptions();

		return view('menus::admin.items.types', [
			'id' => $id,
			'types' => $types
		]);
	}
}
