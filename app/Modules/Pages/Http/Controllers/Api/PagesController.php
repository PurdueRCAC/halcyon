<?php

namespace App\Modules\Pages\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Models\Version;
use App\Modules\Pages\Http\Resources\PageResource;
use App\Modules\Pages\Http\Resources\PageResourceCollection;

/**
 * Pages
 *
 * @apiUri    /api/pages
 */
class PagesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/pages
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
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Article state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"published",
	 * 				"unpublished",
	 * 				"trashed"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "access",
	 * 		"description":   "Article access value.",
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		},
	 * 		"required":      false
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null
	 * 		},
	 * 		"required":      false
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "title",
	 * 			"enum": [
	 * 				"id",
	 * 				"title",
	 * 				"alias",
	 * 				"created_at",
	 * 				"updated_at"
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
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'state'     => 'published',
			'access'    => null,
			'parent'    => 0,
			'limit'     => config('list_limit', 20),
			'order'     => Page::$orderBy,
			'order_dir' => Page::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title', 'created_at', 'updated_at']))
		{
			$filters['order'] = Page::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Page::$orderDir;
		}

		// Get records
		$p = new Page;

		$query = $p->query();

		$page = $p->getTable();
		/*$version = (new Version())->getTable();

		$query
			->select([$page . '.*', $version . '.title'])
			->join($version, $version . '.id', $page . '.version_id');*/

		if ($filters['search'])
		{
			$query->where(function($query) use ($filters, $page)
			{
				$query->where($page . '.content', 'like', '%' . $filters['search'] . '%')
					->orWhere($page . '.title', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'] == 'published')
		{
			$query->where($page . '.state', '=', 1);
		}
		elseif ($filters['state'] == 'unpublished')
		{
			$query->where($page . '.state', '=', 0);
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->where($page . '.state', '=', 2);
		}

		$levels = auth()->user() ? auth()->user()->getAuthorisedViewLevels() : array(1);

		if (!in_array($filters['access'], $levels))
		{
			$filters['access'] = 1;
		}

		if ($filters['access'] > 0)
		{
			$query->where($page . '.access', '=', (int)$filters['access']);
		}

		if ($filters['parent'])
		{
			$parent = Page::findOrFail($filters['parent']);

			$query
				->where($page . '.lft', '>', $parent->lft)
				->where($page . '.rgt', '<', $parent->rgt);
		}

		$rows = $query
			//->withCount('versions')
			->orderBy($page . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		return new PageResourceCollection($rows);
	}

	/**
	 * Create an entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/pages
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
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
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_down",
	 * 		"description":   "Stop publishing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"format":    "date-time",
	 * 			"example":   "2021-01-30T08:30:00Z"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'title'   => 'required|max:255',
			'content' => 'required',
			'access'  => 'nullable|min:1'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Page;
		$row->fill($request->all());
		$row->params = json_encode($request->input('params', []));

		if (!$row->save())
		{
			return response()->json(['message' => trans('page::messages.page created')], 409);
		}

		return new PageResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/pages/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return Response
	 */
	public function read($id)
	{
		$page = Page::findOrFail((int)$id);

		$user = auth()->user();

		// Can non-managers view this article?
		if (!$user || !$user->can('manage pages'))
		{
			if (!$page->isPublished())
			{
				abort(404, trans('pages::pages.article not found'));
			}
		}

		// Does the user have access to the article?
		$levels = $user ? $user->getAuthorisedViewLevels() : array(1);

		if (!in_array($page->access, $levels))
		{
			abort(403, trans('pages::pages.permission denied'));
		}

		return new PageResource($page);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/pages/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
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
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
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
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_down",
	 * 		"description":   "Stop publishing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @param  Request $request
	 * @param  integer $id
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
		$validator = Validator::make($request->all(), [
			'parent_id' => 'nullable|integer|min:1',
			'title' => 'nullable|string|max:255',
			'publish_up' => 'nullable|date',
			'publish_down' => 'nullable|date',
		]);

		if ($validator->fails()) //!$request->validated())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$row = Page::findOrFail($id);
		$row->fill($request->all());

		$params = $request->input('params', []);
		if (!empty($params))
		{
			$row->params = json_encode($params);
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('pages::messages.page failed')], 500);
		}

		return new PageResource($page);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/pages/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer $id
	 * @return  Response
	 */
	public function destroy($id)
	{
		$row = Page::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
