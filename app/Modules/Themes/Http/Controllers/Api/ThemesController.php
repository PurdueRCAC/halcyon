<?php

namespace App\Modules\Themes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Themes\Models\Theme;

/**
 * Themes
 *
 * @apiUri    /api/themes
 */
class ThemesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/themes
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0,
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
	 * 			"default":   "name",
	 * 			"enum": [
	 * 				"id",
	 * 				"name"
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
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => null,
			'template'  => 0,
			'client_id' => null,
			// Pagination
			'limit'     => config('list_limit', 20),
			'page'      => 1,
			'order'     => 'title',
			'order_dir' => 'asc',
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'title']))
		{
			$filters['order'] = 'title';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'asc';
		}

		$query = Theme::query();

		$e = 'extensions';
		//$l = 'languages';
		$m = 'menu';
		$s = (new Theme)->getTable();

		$query
			->select([
				$s . '.id',
				$s . '.template',
				$s . '.title',
				$s . '.home',
				$s . '.client_id',
				$s . '.params',
				//'\'0\' AS assigned',
				$m . '.template_style_id AS assigned',
				//$l . '.title AS language_title',
				//$l . '.image',
				$e . '.id AS e_id'
			]);

		// Join on menus.
		$query
			->leftJoin($m, $m . '.template_style_id', $s . '.id');

		// Join over the language
		//$query
		//	->leftJoin($l, $l . '.lang_code', $s . '.home');

		// Filter by extension enabled
		$query
			->leftJoin($e, $e . '.element', $s . '.template')
			//->where($e . '.client_id', '=', $s . '.client_id')
			->where($e . '.enabled', '=', 1)
			->where($e . '.type', '=', 'template');

		if ($filters['search'])
		{
			$filters['search'] = strtolower((string)$filters['search']);

			if (stripos($filters['search'], 'id:') === 0)
			{
				$query->where($s . '.id', '=', (int) substr($filters['search'], 3));
			}
			else
			{
				$query->where(function($q) use ($filters)
				{
					$q->where($s . '.title', 'like', $filters['search'])
						->orWhere($s . '.template', 'like', $filters['search']);
				});
			}
		}

		if (!is_null($filters['client_id']))
		{
			$query->where($s . '.client_id', '=', (int)$filters['client_id']);
		}

		if ($filters['template'])
		{
			$query->where($s . '.template', '=', (int)$filters['template']);
		}

		$query
			->groupBy([
				$s . '.id',
				$s . '.template',
				$s . '.title',
				$s . '.home',
				$s . '.client_id',
				//$l . '.title',
				//$l . '.image',
				$e . '.id AS extension_id'
			]);

		// Get records
		$rows = $query
			->paginate($filters['limit'], ['*'], 'page', $filters['page'])
			->appends(array_filter($filters));

		//$preview = $this->config->get('template_positions_display');

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/themes
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful creation"
	 * 		},
	 * 		"500": {
	 * 			"description": "Failed to create record"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return Response
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:255',
 			'element' => 'required|string|max:255',
 			'enabled' => 'nullable|integer',
 			'access' => 'nullable|integer',
 			'client_id' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Theme();
		$row->type = 'theme';
		$row->name = $request->input('name');
		$row->protected = 0;
		if ($request->has('element'))
		{
			$row->element = $request->input('element');
		}
		if ($request->has('enabled'))
		{
			$row->enabled = $request->input('enabled');
		}
		if ($request->has('client_id'))
		{
			$row->client_id = $request->input('client_id');
		}
		if ($request->has('access'))
		{
			$row->access = $request->input('access');
		}

		if (!$row->save())
		{
			return response()->json($row->getError(), 500);
		}

		$row->api = route('api.themes.read', ['id' => $row->id]);

		$can = [
			'edit'   => false,
			'delete' => false
		];

		if (auth()->user())
		{
			$can['edit']   = auth()->user()->can('edit themes');
			$can['delete'] = auth()->user()->can('delete themes');
		}

		$row->can = $can;

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/themes/{id}
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
	 * 			"description": "Successful creation"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  integer  $id
	 * @return Response
	 */
	public function read($id)
	{
		$row = Theme::findOrFail((int)$id);

		$row->api = route('api.themes.read', ['id' => $row->id]);

		// Permissions check
		$can = [
			'edit'   => false,
			'delete' => false
		];

		if (auth()->user())
		{
			$can['edit']   = auth()->user()->can('edit themes');
			$can['delete'] = auth()->user()->can('delete themes');
		}

		$row->can = $can;

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/themes/{id}
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
	 * 		"description":   "Menu title",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"enum": [
	 * 				0,
	 * 				1
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful creation"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		},
	 * 		"500": {
	 * 			"description": "Failed to update record"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   integer $id
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$rules = [
			'name' => 'nullable|string|max:255',
 			'enabled' => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Theme::findOrFail($id);
		if ($request->has('name'))
		{
			$row->name = $request->input('name');
		}
		if ($request->has('enabled'))
		{
			$row->enabled = $request->input('enabled');
		}

		if (!$row->save())
		{
			return response()->json(['message' => $row->getError()], 500);
		}

		$row->api = route('api.themes.read', ['id' => $row->id]);

		// Permissions check
		$can = [
			'edit'   => false,
			'delete' => false
		];

		if (auth()->user())
		{
			$can['edit']   = auth()->user()->can('edit themes');
			$can['delete'] = auth()->user()->can('delete themes');
		}

		$row->can = $can;

		return $row;
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/themes/{id}
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
	public function delete($id)
	{
		$row = Theme::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}
}
