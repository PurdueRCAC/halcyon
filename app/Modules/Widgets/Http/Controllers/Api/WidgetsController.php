<?php

namespace App\Modules\Widgets\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
 * @apiUri    /widgets
 */
class WidgetsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /widgets
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
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
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
	 * 			"content": {
	 * 				"application/json": {
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
	 * }}}}}
	 * @param  Request $request
	 * @return WidgetResourceCollection
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
			'page'      => 1,
			'order'     => Widget::$orderBy,
			'order_dir' => Widget::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		$filters['order'] = Widget::getSortField($filters['order']);
		$filters['order_dir'] = Widget::getSortDirection($filters['order_dir']);

		$rows = Widget::query()
			->withFilters($filters)
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		return new WidgetResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /widgets
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Widget title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 100
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "position",
	 * 		"description":   "Widget position",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "widget",
	 * 		"description":   "The type of widget",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "params",
	 * 		"description":   "A list of key value pairs",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menu",
	 * 		"description":   "A list of menu item assignments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResponse|WidgetResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'title'    => 'required|string|max:100',
			'position' => 'required|string|max:50',
			'widget'   => 'required|string|max:50',
			'params'   => 'nullable|array',
			'menu'     => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		// Save menu assignments
		if ($request->has('menu'))
		{
			$menu = $request->input('menu', array());
			$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
			$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

			if (!$row->saveAssignment($assignment, $assigned))
			{
				return response()->json(['message' => trans('global.messages.save failed')], 500);
			}
		}

		return new WidgetResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /widgets/{id}
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
	 * 			"description": "Successful record lookup",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 134,
	 * 						"title": "Announcements",
	 * 						"note": "",
	 * 						"content": "",
	 * 						"ordering": 1,
	 * 						"position": "featureRight",
	 * 						"checked_out": 0,
	 * 						"checked_out_time": null,
	 * 						"publish_up": null,
	 * 						"publish_down": null,
	 * 						"published": 1,
	 * 						"module": "news",
	 * 						"access": 1,
	 * 						"showtitle": 0,
	 * 						"params": {
	 * 							"catid": "2",
	 * 							"item_title": "0",
	 * 							"link_titles": null,
	 * 							"item_heading": "h4",
	 * 							"showLastSeparator": "1",
	 * 							"readmore": "1",
	 * 							"limit": "5",
	 * 							"ordering": "published",
	 * 							"direction": "DESC",
	 * 							"layout": null,
	 * 							"moduleclass_sfx": null,
	 * 							"cache": "0",
	 * 							"cache_time": "900",
	 * 							"cachemode": "itemid"
	 * 						},
	 * 						"client_id": 0,
	 * 						"language": "*",
	 * 						"language_title": null,
	 * 						"editor": null,
	 * 						"access_level": "Public",
	 * 						"pages": 0,
	 * 						"name": "news",
	 * 						"api": "https://yourhost/api/widgets/134",
	 * 						"menu_assignment": 1,
	 * 						"can": {
	 * 							"edit": false,
	 * 							"delete": false
	 * 						}
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return WidgetResource
	 */
	public function read(int $id)
	{
		$row = Widget::findOrFail($id);

		return new WidgetResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /widgets/{id}
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Widget title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 100
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "position",
	 * 		"description":   "Widget position",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "widget",
	 * 		"description":   "The type of widget",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 50
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "params",
	 * 		"description":   "A list of key value pairs",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menu",
	 * 		"description":   "A list of menu item assignments",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "array"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful record update",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 134,
	 * 						"title": "Announcements",
	 * 						"note": "",
	 * 						"content": "",
	 * 						"ordering": 1,
	 * 						"position": "featureRight",
	 * 						"checked_out": 0,
	 * 						"checked_out_time": null,
	 * 						"publish_up": null,
	 * 						"publish_down": null,
	 * 						"published": 1,
	 * 						"module": "news",
	 * 						"access": 1,
	 * 						"showtitle": 0,
	 * 						"params": {
	 * 							"catid": "2",
	 * 							"item_title": "0",
	 * 							"link_titles": null,
	 * 							"item_heading": "h4",
	 * 							"showLastSeparator": "1",
	 * 							"readmore": "1",
	 * 							"limit": "5",
	 * 							"ordering": "published",
	 * 							"direction": "DESC",
	 * 							"layout": null,
	 * 							"moduleclass_sfx": null,
	 * 							"cache": "0",
	 * 							"cache_time": "900",
	 * 							"cachemode": "itemid"
	 * 						},
	 * 						"client_id": 0,
	 * 						"language": "*",
	 * 						"language_title": null,
	 * 						"editor": null,
	 * 						"access_level": "Public",
	 * 						"pages": 0,
	 * 						"name": "news",
	 * 						"api": "https://yourhost/api/widgets/134",
	 * 						"menu_assignment": 1,
	 * 						"can": {
	 * 							"edit": false,
	 * 							"delete": false
	 * 						}
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  JsonResponse|WidgetResource
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'title'    => 'nullable|string|max:100',
			'position' => 'nullable|string|max:50',
			'widget'   => 'nullable|string|max:50',
			'params'   => 'nullable|array',
			'menu'     => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		// Save menu assignments
		if ($request->has('menu'))
		{
			$menu = $request->input('menu', array());
			$assignment = (isset($menu['assignment']) ? $menu['assignment'] : 0);
			$assigned   = (isset($menu['assigned']) ? $menu['assigned'] : array());

			if (!$row->saveAssignment($assignment, $assigned))
			{
				return response()->json(['message' => trans('global.messages.save failed')], 500);
			}
		}

		return new WidgetResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /widgets/{id}
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
	 * @param   int  $id
	 * @return  JsonResponse
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
