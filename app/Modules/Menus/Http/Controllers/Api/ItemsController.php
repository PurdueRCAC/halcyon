<?php

namespace App\Modules\Menus\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Modules\Menus\Http\Resources\ItemResource;
use App\Modules\Menus\Http\Resources\ItemResourceCollection;
use App\Modules\Menus\Models\Type;
use App\Modules\Menus\Models\Item;

/**
 * Menu items
 *
 * @apiUri    /menus/items
 */
class ItemsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /menus/items
	 * @apiAuthorization  true
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
	 * 			"default":   20
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
	 * @param  Request  $request
	 * @return ResourceCollection
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
			$a . '.content',
			$a . '.published',
			$a . '.module_id',
			$a . '.ordering',
			$a . '.checked_out',
			$a . '.checked_out_time',
			$a . '.access',
			$a . '.params',
			$a . '.lft',
			$a . '.rgt',
			$a . '.home',
			$a . '.language',
			$a . '.client_id',
		]);

		// Exclude the root category.
		$query->where($a . '.id', '>', 1);
		$query->where($a . '.client_id', '=', 0);

		// Filter on the published state.
		if (!auth()->user() || !auth()->user()->can('manage'))
		{
			$filters['state'] = 'published';
		}

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
				$query->where(function($where) use ($search, $a)
				{
					$where->where($a . '.title', 'like', '%' . $search . '%')
						->orWhere($a . '.alias', 'like', '%' . $search . '%');
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
		if (auth()->user())
		{
			$access = auth()->user()->getAuthorisedViewLevels();
		}
		$query->whereIn($a . '.access', $access);

		// Filter on the level.
		if ($filters['level'])
		{
			$query->where($a . '.level', '<=', (int) $filters['level']);
		}

		// Filter on the language.
		if ($filters['language'])
		{
			$query->where($a . '.language', '=', $filters['language']);
		}

		// Get records
		$rows = $query
			->with('viewlevel')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		$rows->appends(array_filter($filters));

		return new ItemResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /menus/items
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu item text",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Menu type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent menu item ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 9,
	 * 						"menutype": "about",
	 * 						"title": "About Us",
	 * 						"alias": "about",
	 * 						"note": "",
	 * 						"path": "about",
	 * 						"link": "about",
	 * 						"type": "module",
	 * 						"parent_id": 1,
	 * 						"level": 1,
	 * 						"state": 1,
	 * 						"module_id": 22,
	 * 						"ordering": 0,
	 * 						"checked_out": 0,
	 * 						"checked_out_time": null,
	 * 						"access": 1,
	 * 						"params": [],
	 * 						"lft": 23,
	 * 						"rgt": 24,
	 * 						"home": 0,
	 * 						"language": "*",
	 * 						"client_id": 0,
	 * 						"editor": null,
	 * 						"componentname": "pages",
	 * 						"access_level": "Public",
	 * 						"name": "pages",
	 * 						"published": 1,
	 * 						"item_type": "pages",
	 * 						"api": "https://example.org/api/menus/items/9"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse|ItemResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'menutype' => 'required|string|max:24',
			'parent_id' => 'nullable|integer|min:0',
			'title' => 'required|string|max:255',
			'type' => 'required|string|max:255',
			'content' => 'nullable|string',
			'published' => 'nullable|integer',
			'access' => 'nullable|integer',
			'target' => 'nullable|integer',
			'class' => 'nullable|string|max:255',
			'path' => 'nullable|string|max:1024',
			'link' => 'nullable|string|max:1024',
			'module_id' => 'nullable|integer',
			'page_id' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = new Item;
		$row->published = 1;
		$row->access = 1;
		$row->type = 'module';
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
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
				$row->link = $row->link ? $row->link : '/';
				break;

			case 'module':
			default:
				$row->link = $row->link ? $row->link : '/';
				break;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		return new ItemResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /menus/items/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 9,
	 * 						"menutype": "about",
	 * 						"title": "About Us",
	 * 						"alias": "about",
	 * 						"note": "",
	 * 						"path": "about",
	 * 						"link": "about",
	 * 						"type": "module",
	 * 						"parent_id": 1,
	 * 						"level": 1,
	 * 						"state": 1,
	 * 						"module_id": 22,
	 * 						"ordering": 0,
	 * 						"checked_out": 0,
	 * 						"checked_out_time": null,
	 * 						"access": 1,
	 * 						"params": [],
	 * 						"lft": 23,
	 * 						"rgt": 24,
	 * 						"home": 0,
	 * 						"language": "*",
	 * 						"client_id": 0,
	 * 						"editor": null,
	 * 						"componentname": "pages",
	 * 						"access_level": "Public",
	 * 						"name": "pages",
	 * 						"published": 1,
	 * 						"item_type": "pages",
	 * 						"api": "https://example.org/api/menus/items/9"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int $id
	 * @return JsonResponse|ItemResource
	 */
	public function read(int $id)
	{
		$row = Item::findOrFail($id);

		$user = auth()->user();

		// Can the user view this item?
		if (!$user || !$user->can('manage menus'))
		{
			if (!$row->published)
			{
				return response()->json(['message' => trans('menus::menus.item not found')], 404);
			}
		}

		// Does the user have access to the item?
		$levels = $user ? $user->getAuthorisedViewLevels() : array(1);

		if (!in_array($row->access, $levels))
		{
			return response()->json(['message' => trans('global.permission denied')], 403);
		}

		return new ItemResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /menus/items/{id}
	 * @apiAuthorization  true
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
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu item text",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "type",
	 * 		"description":   "Menu type",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent menu item ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 9,
	 * 						"menutype": "about",
	 * 						"title": "About Us",
	 * 						"alias": "about",
	 * 						"note": "",
	 * 						"path": "about",
	 * 						"link": "about",
	 * 						"type": "module",
	 * 						"parent_id": 1,
	 * 						"level": 1,
	 * 						"state": 1,
	 * 						"module_id": 22,
	 * 						"ordering": 0,
	 * 						"checked_out": 0,
	 * 						"checked_out_time": null,
	 * 						"access": 1,
	 * 						"params": [],
	 * 						"lft": 23,
	 * 						"rgt": 24,
	 * 						"home": 0,
	 * 						"language": "*",
	 * 						"client_id": 0,
	 * 						"editor": null,
	 * 						"componentname": "pages",
	 * 						"access_level": "Public",
	 * 						"name": "pages",
	 * 						"published": 1,
	 * 						"item_type": "pages",
	 * 						"api": "https://example.org/api/menus/items/9"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  JsonResponse|ItemResource
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'menutype' => 'nullable|string|max:24',
			'parent_id' => 'nullable|integer|min:0',
			'title' => 'nullable|string|max:255',
			'type' => 'nullable|string|max:255',
			'content' => 'nullable|string',
			'published' => 'nullable|integer',
			'access' => 'nullable|integer',
			'target' => 'nullable|integer',
			'class' => 'nullable|string|max:255',
			'path' => 'nullable|string|max:1024',
			'link' => 'nullable|string|max:1024',
			'module_id' => 'nullable|integer',
			'page_id' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = Item::findOrFail($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
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
				$row->link = $row->link ? $row->link : '/';
				break;

			case 'module':
			default:
				$row->link = $row->link ? $row->link : '/';
				break;
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('pages::messages.page failed')], 500);
		}

		return new ItemResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /menus/items/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete(int $id)
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
	 * @apiUri    /menus/items/reorder
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "A list of IDs",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "A list of ordering values",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry updates"
	 * 		}
	 * }
	 * @param   Request  $request
	 * @return  JsonResponse
	 */
	public function reorder(Request $request)
	{
		// Get the input
		$pks   = $request->input('id', array());
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
