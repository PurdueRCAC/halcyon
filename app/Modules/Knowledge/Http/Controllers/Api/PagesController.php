<?php

namespace App\Modules\Knowledge\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Knowledge\Models\Page;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Knowledge\Http\Resources\PageResource;
use App\Modules\Knowledge\Http\Resources\PageResourceCollection;
use Carbon\Carbon;

/**
 * Pages
 *
 * @apiUri    /knowledge
 */
class PagesController extends Controller
{
	/**
	 * Display a listing of articles
	 *
	 * @apiMethod GET
	 * @apiUri    /knowledge
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "parent",
	 * 		"description":   "Parent page ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "access",
	 * 		"description":   "Access level.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "The page state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"published",
	 * 				"archived",
	 * 				"unpublished",
	 * 				"trashed"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for in feedback comments.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   ""
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
	 * 			"default":   "created_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"created_at",
	 * 				"ip",
	 * 				"user_id",
	 * 				"target_id",
	 * 				"type"
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
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'parent'    => null,
			'state'     => 'published',
			'access'    => 1,
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => Page::$orderBy,
			'order_dir' => Page::$orderDir,
			'level'     => 0,
		);

		$refresh = false;
		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if ($refresh)
		{
			$filters['page'] = 1;
		}

		if (!in_array($filters['order'], ['id', 'lft', 'rgt', 'title', 'state', 'access', 'updated_at', 'created_at']))
		{
			$filters['order'] = 'lft';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Associations::query();

		$p = (new Page)->getTable();
		$a = (new Associations)->getTable();

		$query->join($p, $p . '.id', $a . '.page_id')
			->select($a . '.*'); //$p . '.state', $p . '.access', 
			//->select($p . '.title', $p . '.snippet', $p . '.updated_at', $a . '.*');

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters, $p)
			{
				$query->where($p . '.title', 'like', '%' . $filters['search'] . '%')
					->orWhere($p . '.content', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['level'] > 0)
		{
			$query->where($a . '.level', '<=', $filters['level']);
		}

		if ($filters['parent'])
		{
			$parent = Associations::find($filters['parent']);

			$query->where($a . '.lft', '>=', $parent->lft)
					->where($a . '.rgt', '<=', $parent->rgt);
		}

		if (!auth()->user() || !auth()->user()->can('manage knowledge'))
		{
			$filters['state'] = 'published';
			$filters['access'] = 1;
		}

		if ($filters['state'] == 'published')
		{
			$query->where($a . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'archived')
		{
			$query->where($a . '.state', '=', 2);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($a . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		if ($filters['access'] > 0)
		{
			$query->where($p . '.access', '=', (int)$filters['access']);
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return new PageResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /knowledge
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug. If not provided, one is genereated from the title.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "[a-z0-9_-]+",
	 * 			"example":   "page_name"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Content",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "state",
	 * 		"description":   "Published state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "access",
	 * 		"description":   "Access level",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				1,
	 * 				2
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
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
	 * 						"id": 1,
	 * 						"parent_id": 0,
	 * 						"page_id": 1,
	 * 						"lft": 0,
	 * 						"rgt": 1,
	 * 						"level": 0,
	 * 						"path": "",
	 * 						"state": 1,
	 * 						"access": 1,
	 * 						"page": {
	 * 							"id": 1,
	 * 							"title": "User Guides",
	 * 							"alias": "kb",
	 * 							"created_at": "2020-05-28T16:57:38.000000Z",
	 * 							"updated_at": "2021-10-22T18:40:52.000000Z",
	 * 							"deleted_at": null,
	 * 							"state": 1,
	 * 							"access": 1,
	 * 							"content": "<p>Here you will find all the user guides.</p>",
	 * 							"params": [],
	 * 							"main": 1,
	 * 							"snippet": 0
	 * 						},
	 * 						"api": "https://example.org/api/knowledge/1",
	 * 						"url": "https://example.org/knowledge"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'title'     => 'required|string|max:255',
			'alias'     => 'nullable|string|max:255',
			'content'   => 'required|string',
			'access'    => 'nullable|integer|min:1',
			'state'     => 'nullable|integer',
			'parent_id' => 'required|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$parent_id = $request->input('parent_id');

		$row = new Associations;
		if ($request->has('access'))
		{
			$row->access = $request->input('access');
		}
		if ($request->has('state'))
		{
			$row->state = $request->input('state');
		}
		$row->page_id = $request->input('page_id');
		$row->parent_id = $parent_id;

		$page = Page::find($row->page_id);
		if (!$row->page_id)
		{
			$page = new Page;
		}
		$page->title = $request->input('title');
		$page->alias = $request->input('alias');
		$page->alias = $page->alias ?: $page->title;
		$page->content = $request->input('content');

		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				if ($key == 'variables')
				{
					$vars = array();
					foreach ($val as $opts)
					{
						if (!$opts['key'])
						{
							continue;
						}
						$vars[$opts['key']] = $opts['value'];
					}
					$val = $vars;
				}
				$page->params->set($key, $val);
			}
		}

		if (!$page->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		$row->page_id = $page->id;
		if ($row->parent)
		{
			$row->path = trim($row->parent->path . '/' . $page->alias, '/');
		}
		else
		{
			$row->path = '';
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		if ($row->id && $row->parent_id)// != $orig_parent_id)
		{
			if (!$row->moveByReference($row->parent_id, 'last-child', $row->id))
			{
				return redirect()->back()->withError($row->getError());
			}
		}

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return response()->json(['message' => trans('knowledge::knowledge.error.rebuild failed')], 409);
		}

		return new PageResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /knowledge/{id}
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
	 * 						"id": 1,
	 * 						"parent_id": 0,
	 * 						"page_id": 1,
	 * 						"lft": 0,
	 * 						"rgt": 1,
	 * 						"level": 0,
	 * 						"path": "",
	 * 						"state": 1,
	 * 						"access": 1,
	 * 						"page": {
	 * 							"id": 1,
	 * 							"title": "User Guides",
	 * 							"alias": "kb",
	 * 							"created_at": "2020-05-28T16:57:38.000000Z",
	 * 							"updated_at": "2021-10-22T18:40:52.000000Z",
	 * 							"deleted_at": null,
	 * 							"state": 1,
	 * 							"access": 1,
	 * 							"content": "<p>Here you will find all the user guides.<\/p>",
	 * 							"params": [],
	 * 							"main": 1,
	 * 							"snippet": 0
	 * 						},
	 * 						"api": "https://example.org/api/knowledge/1",
	 * 						"url": "https://example.org/knowledge"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Associations::findOrFail((int)$id);

		return new PageResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /knowledge/{id}
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
	 * 		"description":   "Title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug. If not provided, one is genereated from the title.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "[a-z0-9_-]+",
	 * 			"example":   "page_name"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Content",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "state",
	 * 		"description":   "Published state",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "access",
	 * 		"description":   "Access level",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1,
	 * 			"enum": [
	 * 				1,
	 * 				2
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
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
	 * 						"id": 1,
	 * 						"parent_id": 0,
	 * 						"page_id": 1,
	 * 						"lft": 0,
	 * 						"rgt": 1,
	 * 						"level": 0,
	 * 						"path": "",
	 * 						"state": 1,
	 * 						"access": 1,
	 * 						"page": {
	 * 							"id": 1,
	 * 							"title": "User Guides",
	 * 							"alias": "kb",
	 * 							"created_at": "2020-05-28T16:57:38.000000Z",
	 * 							"updated_at": "2021-10-22T18:40:52.000000Z",
	 * 							"deleted_at": null,
	 * 							"state": 1,
	 * 							"access": 1,
	 * 							"content": "<p>Here you will find all the user guides. Look around all you like.<\/p>",
	 * 							"params": [],
	 * 							"main": 1,
	 * 							"snippet": 0
	 * 						},
	 * 						"api": "https://example.org/api/knowledge/1",
	 * 						"url": "https://example.org/knowledge"
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
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'title'     => 'nullable|string|max:255',
			'alias'     => 'nullable|string|max:255',
			'content'   => 'nullable|string',
			'access'    => 'nullable|integer|min:1',
			'state'     => 'nullable|integer',
			'parent_id' => 'nullable|integer',
			'params'    => 'nullable|array',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Associations::findOrFail($id);
		$orig_parent_id = $row->parent_id;

		$parent_id = $request->input('parent_id', $row->parent_id);

		if ($request->has('access'))
		{
			$row->access = $request->input('access');
		}
		if ($request->has('state'))
		{
			$row->state = $request->input('state');
		}
		$row->page_id = $request->input('page_id', $row->page_id);
		$row->parent_id = $parent_id;

		$page = Page::find($row->page_id);
		$page->title = $request->input('title', $page->title);
		$page->alias = $request->input('alias', $page->alias);
		$page->alias = $page->alias ?: $page->title;
		$page->content = $request->input('content', $page->content);

		if ($params = $request->input('params', []))
		{
			foreach ($params as $key => $val)
			{
				if ($key == 'variables')
				{
					$vars = array();
					foreach ($val as $opts)
					{
						if (!$opts['key'])
						{
							continue;
						}
						$vars[$opts['key']] = $opts['value'];
					}
					$val = $vars;
				}
				$page->params->set($key, $val);
			}
		}

		if (!$page->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		$row->page_id = $page->id;
		if ($row->parent)
		{
			$row->path = trim($row->parent->path . '/' . $page->alias, '/');
		}
		else
		{
			$row->path = '';
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 409);
		}

		if ($id && $parent_id != $orig_parent_id)
		{
			if (!$row->moveByReference($row->parent_id, 'last-child', $row->id))
			{
				return redirect()->back()->withError($row->getError());
			}
		}

		// Rebuild the paths of the entry's children
		if (!$row->rebuild($row->id, $row->lft, $row->level, $row->path))
		{
			return response()->json(['message' => trans('knowledge::knowledge.error.rebuild failed')], 409);
		}

		return new PageResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /knowledge/{id}
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
	 * @param  integer $id
	 * @return Response
	 */
	public function delete($id)
	{
		$row = Associations::findOrFail($id);

		if (!$row->page->snippet)
		{
			if (!$row->page->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
