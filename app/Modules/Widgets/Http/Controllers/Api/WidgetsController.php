<?php

namespace App\Modules\Widgets\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Modules\Widgets\Models\Widget;
use App\Modules\Widgets\Models\Menu;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Viewlevel;
use App\Modules\Widgets\Http\Resources\WidgetResource;
use App\Modules\Widgets\Http\Resources\WidgetResourceCollection;

/**
 * Widgets
 *
 * Manage content widgets for the site.
 *
 * @apiUri    /api/widgets
 */
class WidgetsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/widgets
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   25
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"motd",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "List of widgets",
	 * "example": {
	 *     "current_page": 1,
	 *     "data": [
	 *         {
	 *             "id": 17,
	 *             "title": "Breadcrumbs",
	 *             "note": "",
	 *             "content": "",
	 *             "ordering": 1,
	 *             "position": "breadcrumbs",
	 *             "checked_out": 0,
	 *             "checked_out_time": null,
	 *             "publish_up": null,
	 *             "publish_down": null,
	 *             "published": 1,
	 *             "module": "breadcrumbs",
	 *             "access": 1,
	 *             "showtitle": 1,
	 *             "params": {
	 *                 "showHere": "0",
	 *                 "showHome": "1",
	 *                 "homeText": "Home",
	 *                 "showLast": "1",
	 *                 "separator": "/",
	 *                 "moduleclass_sfx": null,
	 *                 "cache": "1",
	 *                 "cache_time": "900",
	 *                 "cachemode": "itemid"
	 *             },
	 *             "client_id": 0,
	 *             "language": "*",
	 *             "language_title": null,
	 *             "editor": "Shawn M Rice",
	 *             "access_level": "Public",
	 *             "pages": 0,
	 *             "name": "breadcrumbs",
	 *             "api": "https://yourhost/api/widgets/17",
	 *             "menu_assignment": 0,
	 *             "can": {
	 *                 "edit": false,
	 *                 "delete": false
	 *             }
	 *         },
	 *         {
	 *             "id": 132,
	 *             "title": "Science Highlights",
	 *             "note": "",
	 *             "content": "",
	 *             "ordering": 1,
	 *             "position": "featureLeft",
	 *             "checked_out": 0,
	 *             "checked_out_time": null,
	 *             "publish_up": null,
	 *             "publish_down": null,
	 *             "published": 1,
	 *             "module": "news",
	 *             "access": 1,
	 *             "showtitle": 0,
	 *             "params": {
	 *                 "catid": "3",
	 *                 "item_title": "0",
	 *                 "link_titles": null,
	 *                 "item_heading": "h4",
	 *                 "showLastSeparator": "1",
	 *                 "readmore": "1",
	 *                 "limit": "5",
	 *                 "ordering": "published",
	 *                 "direction": "DESC",
	 *                 "layout": null,
	 *                 "moduleclass_sfx": null,
	 *                 "cache": "0",
	 *                 "cache_time": "900",
	 *                 "cachemode": "itemid"
	 *             },
	 *             "client_id": 0,
	 *             "language": "*",
	 *             "language_title": null,
	 *             "editor": "Shawn M Rice",
	 *             "access_level": "Public",
	 *             "pages": 100,
	 *             "name": "news",
	 *             "api": "https://yourhost/api/widgets/132",
	 *             "menu_assignment": 1,
	 *             "can": {
	 *                 "edit": false,
	 *                 "delete": false
	 *             }
	 *         },
	 *         {
	 *             "id": 134,
	 *             "title": "Announcements",
	 *             "note": "",
	 *             "content": "",
	 *             "ordering": 1,
	 *             "position": "featureRight",
	 *             "checked_out": 0,
	 *             "checked_out_time": null,
	 *             "publish_up": null,
	 *             "publish_down": null,
	 *             "published": 1,
	 *             "module": "news",
	 *             "access": 1,
	 *             "showtitle": 0,
	 *             "params": {
	 *                 "catid": "2",
	 *                 "item_title": "0",
	 *                 "link_titles": null,
	 *                 "item_heading": "h4",
	 *                 "showLastSeparator": "1",
	 *                 "readmore": "1",
	 *                 "limit": "5",
	 *                 "ordering": "published",
	 *                 "direction": "DESC",
	 *                 "layout": null,
	 *                 "moduleclass_sfx": null,
	 *                 "cache": "0",
	 *                 "cache_time": "900",
	 *                 "cachemode": "itemid"
	 *             },
	 *             "client_id": 0,
	 *             "language": "*",
	 *             "language_title": null,
	 *             "editor": null,
	 *             "access_level": "Public",
	 *             "pages": 0,
	 *             "name": "news",
	 *             "api": "https://yourhost/api/widgets/134",
	 *             "menu_assignment": 1,
	 *             "can": {
	 *                 "edit": false,
	 *                 "delete": false
	 *             }
	 *         }
	 *     ],
	 *     "first_page_url": "https://yourhost/api/widgets?page=1",
	 *     "from": 1,
	 *     "last_page": 2,
	 *     "last_page_url": "https://yourhost/api/widgets?page=2",
	 *     "next_page_url": "https://yourhost/api/widgets?page=2",
	 *     "path": "https://yourhost/api/widgets",
	 *     "per_page": 3,
	 *     "prev_page_url": null,
	 *     "to": 3,
	 *     "total": 5
	 * }}}
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'position'  => null,
			'widget'    => null,
			'language'  => null,
			'client_id' => 0,
			// Pagination
			'limit'     => config('list_limit', 20),
			'order'     => Widget::$orderBy,
			'order_dir' => Widget::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'position', 'state', 'access']))
		{
			$filters['order'] = Widget::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Widget::$orderDir;
		}

		$rows = Widget::paginate($filters['limit']);

		$query = Widget::query();

		$p = (new Widget)->getTable();
		$u = (new User)->getTable();
		$a = (new Viewlevel)->getTable();
		$m = (new Menu)->getTable();
		$e = 'extensions';
		$l = 'languages';

		$query->select(
				$p . '.*',
				$l . '.title AS language_title',
				$u . '.name AS editor',
				$a . '.title AS access_level',
				DB::raw('MIN(' . $m . '.menuid) AS pages'),
				$e . '.name AS name'
			)
			->where($p . '.client_id', '=', $filters['client_id']);

		// Join over the language
		$query
			//->select($l . '.title AS language_title')
			->leftJoin($l, $l . '.lang_code', $p . '.language');

		// Join over the users for the checked out user.
		$query
			//->select($u . '.name AS editor')
			->leftJoin($u, $u . '.id', $p . '.checked_out');

		// Join over the access groups.
		$query
			//->select($a . '.title AS access_level')
			->leftJoin($a, $a . '.id', $p . '.access');

		// Join over menus
		$query
			//->select('MIN(' . $m . '.menuid) AS pages')
			->leftJoin($m, $m . '.moduleid', $p . '.id');

		// Join over the extensions
		$query
			//->select($e . '.name AS name')
			->leftJoin($e, $e . '.element', $p . '.module')
			->where($e . '.type', '=', 'widget')
			->groupBy(
				$p . '.id',
				$p . '.title',
				$p . '.note',
				$p . '.position',
				$p . '.widget',
				$p . '.language',
				$p . '.checked_out',
				$p . '.checked_out_time',
				$p . '.published',
				$p . '.access',
				$p . '.ordering',
				$p . '.content',
				$p . '.showtitle',
				$p . '.params',
				$p . '.client_id',
				$l . '.title',
				$u . '.name',
				$a . '.title',
				$e . '.name',
				//$l . '.lang_code',
				$u . '.id',
				$a . '.id',
				$m . '.moduleid',
				$e . '.element',
				$p . '.publish_up',
				$p . '.publish_down',
				$e . '.enabled'
			);

		// Filter by access level.
		if ($filters['access'])
		{
			$query->where($p . '.access', '=', (int) $filters['access']);
		}

		// Filter by published state
		/*if (is_numeric($filters['state']))
		{
			$query->where($p . '.published', '=', (int) $filters['state']);
		}
		elseif ($filters['state'] === '')
		{
			$query->whereIn($p . '.published', array(0, 1));
		}*/
		if ($filters['state'] == 'published')
		{
			$query->where($p . '.published', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($p . '.published', '=', 0);
		}

		// Filter by position.
		if ($filters['position'])
		{
			if ($filters['position'] == 'none')
			{
				$filters['position'] = '';
			}
			$query->where($p . '.position', '=', $filters['position']);
		}

		// Filter by module.
		if ($filters['widget'])
		{
			$query->where($p . '.module', '=', $filters['widget']);
		}

		// Filter by search
		if (!empty($filters['search']))
		{
			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($p . '.id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($where) use ($p, $filters)
				{
					$where->where($p . '.title', 'like', '%' . $filters['search'] . '%')
						->orWhere($p . '.note', 'like', '%' . $filters['search'] . '%');
				});
			}
		}

		// Filter by module.
		if ($filters['language'])
		{
			$query->where($p . '.language', '=', $filters['language']);
		}

		// Order records
		if ($filters['order'] == 'name')
		{
			$query->orderBy('name', $filters['order_dir']);
			$query->orderBy('ordering', 'asc');
		}
		else if ($filters['order'] == 'ordering')
		{
			$query->orderBy('position', 'asc');
			$query->orderBy('ordering', $filters['order_dir']);
			$query->orderBy('name', 'asc');
		}
		else
		{
			$query->orderBy($filters['order'], $filters['order_dir']);
			$query->orderBy('name', 'asc');
			$query->orderBy('ordering', 'asc');
		}

		$rows = $query
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new WidgetResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/widgets
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Widget title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "position",
	 * 		"description":   "Widget position",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "widget",
	 * 		"description":   "The type of widget",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'title' => 'required|string|max:100',
			'position' => 'required|string|max:50',
			'widget' => 'required|string|max:50'
		]);

		$row = new Widget();
		$row->fill($request->all());

		if ($params = $request->input('params'))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		// Save menu assignments
		if ($request->has('menu'))
		{
			$menu = $request->input('menu', array());
			$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
			$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

			if (!$row->saveAssignment($assignment, $assigned))
			{
				$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

				return response()->json(['message' => $error], 500);
			}
		}

		return new WidgetResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/widgets/{id}
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
	 * 		"data": {
	 * 			"id": 134,
	 * 			"title": "Announcements",
	 * 			"note": "",
	 * 			"content": "",
	 * 			"ordering": 1,
	 * 			"position": "featureRight",
	 * 			"checked_out": 0,
	 * 			"checked_out_time": null,
	 * 			"publish_up": null,
	 * 			"publish_down": null,
	 * 			"published": 1,
	 * 			"module": "news",
	 * 			"access": 1,
	 * 			"showtitle": 0,
	 * 			"params": {
	 * 				"catid": "2",
	 * 				"item_title": "0",
	 * 				"link_titles": null,
	 * 				"item_heading": "h4",
	 * 				"showLastSeparator": "1",
	 * 				"readmore": "1",
	 * 				"limit": "5",
	 * 				"ordering": "published",
	 * 				"direction": "DESC",
	 * 				"layout": null,
	 * 				"moduleclass_sfx": null,
	 * 				"cache": "0",
	 * 				"cache_time": "900",
	 * 				"cachemode": "itemid"
	 * 			},
	 * 			"client_id": 0,
	 * 			"language": "*",
	 * 			"language_title": null,
	 * 			"editor": null,
	 * 			"access_level": "Public",
	 * 			"pages": 0,
	 * 			"name": "news",
	 * 			"api": "https://yourhost/api/widgets/134",
	 * 			"menu_assignment": 1,
	 * 			"can": {
	 * 				"edit": false,
	 * 				"delete": false
	 * 			}
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Widget::findOrFail((int)$id);

		return new WidgetResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/widgets/{id}
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Widget title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "position",
	 * 		"description":   "Widget position",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "widget",
	 * 		"description":   "The type of widget",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'title' => 'nullable|string|max:100',
			'position' => 'nullable|string|max:50',
			'widget' => 'nullable|string|max:50'
		]);

		$row = Widget::findOrFail($id);
		$row->fill($request->all());

		if ($params = $request->input('params'))
		{
			foreach ($params as $key => $val)
			{
				$row->params->set($key, $val);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		// Save menu assignments
		if ($request->has('menu'))
		{
			$menu = $request->input('menu', array());
			$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
			$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

			if (!$row->saveAssignment($assignment, $assigned))
			{
				$error = $row->getError() ? $row->getError() : trans('global.messages.save failed');

				return response()->json(['message' => $error], 500);
			}
		}

		return new WidgetResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/widgets/{id}
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
	 * 			"description": "Successful deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function destroy($id)
	{
		$row = Widget::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 409);
		}

		return response()->json(null, 204);
	}
}
