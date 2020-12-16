<?php

namespace App\Modules\Menus\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Menu items
 *
 * @apiUri    /api/menus/items
 */
class ItemsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/menus/items
	 * @apiParameter {
	 * 		"name":          "state",
	 * 		"description":   "Listener enabled/disabled state",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "published"
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "datetimecreated",
	 * 		"allowedValues": "id, motd, datetimecreated, datetimeremoved"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "desc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'menutype' => '',
			'search'   => null,
			'state'    => '',
			'access'   => null,
			'parent'   => 0,
			'level'    => 0,
			'language' => '',
			// Paging
			'limit'    => config('list_limit', 20),
			// Sorting
			'order'     => Item::$orderBy,
			'order_dir' => Item::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
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
		//$menu = Type::findByMenutype($type);

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
			//$a . '.browserNav',
			$a . '.access',
			//$a . '.img',
			//$a . '.template_style_id',
			$a . '.params',
			$a . '.lft',
			$a . '.rgt',
			$a . '.home',
			$a . '.language',
			$a . '.client_id',
			'l.title AS language_title',
			'l.image AS image',
			'u.name AS editor',
			'c.element AS componentname',
			'ag.title AS access_level',
			'e.name AS name',
			DB::raw('CASE ' . $a . '.type' .
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
		$published = $filters['state'];
		if (is_numeric($published) && auth()->user() && auth()->user()->can('manage'))
		{
			$query->where($a . '.published', '=', (int) $published);
		}
		else
		{
			$query->where($a . '.published', '=', 1);
		}

		// Filter by search in title, alias or id
		if ($search = $filters['search'])
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
				$query->where(function($where) use ($search)
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
		$access = [0, 1];
		if (auth()->user()) // && !auth()->user()->can('admin')
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}
		$query->whereIn($a . '.access', $access);

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
			->paginate($filters['limit']);

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

				case 'component':
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
								$value .= ' » ' . trans($item->componentname . ' ' . $vars['view'] . ' VIEW_DEFAULT_TITLE');
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

		$rows->appends(array_filter($filters));
		$rows->each(function($row, $key)
		{
			$row->api = route('api.menus.items.read', ['id' => $row->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/menus/items
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "name",
	 * 		"description":   "Group name",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'parent_id' => 'nullable|integer|min:0',
			'title' => 'required|string|max:255',
			'type' => 'required|string|max:255',
		]);

		if ($validator->fails()) //!$request->validated())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = new Item;
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.menus.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/menus/items/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$row = Item::findOrFail((int)$id);

		$user = auth()->user();

		// Can non-managers view this article?
		if (!$user || !$user->can('manage menus'))
		{
			if (!$row->isPublished())
			{
				return response()->json(['message' => trans('menus::menus.item not found')], 404);
			}
		}

		// Does the user have access to the article?
		$levels = $user ? $user->getAuthorisedViewLevels() : array(1);

		if (!in_array($row->access, $levels))
		{
			return response()->json(['message' => trans('global.permission denied')], 403);
		}

		$row->api = route('api.menus.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/menus/items/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "title",
	 * 		"description":   "Listener title",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixgroup",
	 * 		"description":   "Unix group name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unixid",
	 * 		"description":   "Unix ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "deptnumber",
	 * 		"description":   "Organization department ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "githuborgname",
	 * 		"description":   "Github organization name",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$validator = Validator::make($request->all(), [
			'parent_id' => 'nullable|integer|min:0',
			'title' => 'nullable|string|max:255',
			'type' => 'nullable|string|max:255',
		]);

		if ($validator->fails()) //!$request->validated())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = Item::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			return response()->json(['message' => trans('pages::messages.page failed')], 500);
		}

		$row->api = route('api.menus.items.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/menus/items/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Item::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}

	/**
	 * Save the order for items
	 *
	 * @apiMethod POST
	 * @apiUri    /api/menus/items/reorder
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   Request  $request
	 * @return  Response
	 */
	public function reorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('cid', array());
		$order = $request->input('order', array());

		// Sanitize the input
		foreach ($pks as $i => $v)
		{
			$pks[$i] = (int) $v;
		}

		foreach ($order as $i => $v)
		{
			$order[$i] = (int) $v;
		}

		//Arr::toInteger($pks);
		//Arr::toInteger($order);

		// Save the ordering
		$return = Item::saveOrder($pks, $order);

		if ($return === false)
		{
			// Reorder failed
			return response()->json(['message' => trans('global.messages.reorder failed')], 500);
		}

		return response()->json(null, 204);
	}
}
