<?php

namespace App\Modules\Tags\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Tags\Models\Tag;
use App\Modules\Tags\Http\Resources\TagsResourceCollection;
use App\Modules\Tags\Http\Resources\TagsResource;

/**
 * Tags
 *
 * @apiUri    /api/tags
 */
class TagsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/tags
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
	 * 		"description":   "Tag state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "active"
	 * 		"allowedValues": "active, trashed"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, name, slug, created_at"
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
		$filters = array(
			'search'   => $request->input('search', ''),
			'state'    => $request->input('state', 'active'),
			'type'     => $request->input('type', null),
			// Paging
			'limit'    => $request->input('limit', config('list_limit', 20)),
			//'start' => $request->input('limitstart', 0),
			// Sorting
			'order'     => $request->input('order', Tag::$orderBy),
			'order_dir' => $request->input('order_dir', Tag::$orderDir)
		);

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Tag::$orderDir;
		}

		$query = Tag::query();

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			$query->where(function($where) use ($filters)
			{
				$where->where('name', 'like', '%' . $filters['search'] . '%')
					->orWhere('slug', 'like', '%' . $filters['search'] . '%');
			});
		}

		if ($filters['state'])
		{
			if ($filters['state'] == 'active')
			{
				$query->whereNull('deleted_at');
			}
			elseif ($filters['state'] == 'trashed')
			{
				$query->whereNotNull('deleted_at');
			}
		}

		$rows = $query
			//->withCount('tagged')
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit']);

		return new TagResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/tags
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Tag to be created.",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|min:3|max:255'
		]);

		$tag = Tag::create($request->all());

		return new TagResource($tag);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/tags/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return Response
	 */
	public function read($id)
	{
		$tag = Tag::findOrFail($id);

		return new TagResource($tag);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/tags/{id}
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
	 * 		"name":          "name",
	 * 		"description":   "Tag text",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "slug",
	 * 		"description":   "Normalized text (alpha-numeric, no punctuation)",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of a tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "namespace",
	 * 		"description":   "Namespace for tag",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "substitutes",
	 * 		"description":   "Comma-separated list of aliases or alternatives",
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
			'description' => 'nullable|string',
			'slug' => 'nullable|min:3|max:255',
			'name' => 'required|min:3|max:255',
		]);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()->first()], 409);
		}

		$tag = Type::findOrFail($id);
		$tag->update($request->all());

		return new TagResource($tag);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/tags/{id}
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @return  Response
	 */
	public function destroy($id)
	{
		$tag = Tag::findOrFail($id);

		if (!$tag->trashed())
		{
			$tag->delete();
		}

		return response()->json(null, 204);
	}
}
