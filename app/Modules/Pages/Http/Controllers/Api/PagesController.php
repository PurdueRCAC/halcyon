<?php

namespace App\Modules\Pages\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
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
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Article state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "published",
	 * 		"allowedValues": "published, unpublished, trashed"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "access",
	 * 		"description":   "Article access value.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1,
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, title, alias, created_at, updated_at"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"allowedValues": "asc, desc"
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
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Content",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "state",
	 * 		"description":   "Published state",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * 		"allowedValues": "0, 1"
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "access",
	 * 		"description":   "Access level",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * 		"allowedValues": "1, 2, ..."
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_down",
	 * 		"description":   "Stop publishing",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'title'   => 'required|max:255',
			'content' => 'required',
			'access'  => 'nullable|min:1'
		]);

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
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
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
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Title",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "URL slug",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "content",
	 * 		"description":   "Content",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "state",
	 * 		"description":   "Published state",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * 		"allowedValues": "0, 1"
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "access",
	 * 		"description":   "Access level",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * 		"allowedValues": "1, 2, ..."
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_down",
	 * 		"description":   "Stop publishing",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "parent_id",
	 * 		"description":   "Parent page ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "publish_up",
	 * 		"description":   "Start publishing (defaults to created time)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
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
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
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
