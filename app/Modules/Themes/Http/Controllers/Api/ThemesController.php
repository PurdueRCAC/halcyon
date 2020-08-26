<?php

namespace App\Modules\Themes\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
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
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       25
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
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
	 * 		"allowedValues": "asc, desc"
	 * }
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
		$l = 'languages';
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
				$l . '.title AS language_title',
				$l . '.image',
				$e . '.id AS e_id'
			]);

		// Join on menus.
		$query
			->leftJoin($m, $m . '.template_style_id', $s . '.id');

		// Join over the language
		$query
			->leftJoin($l, $l . '.lang_code', $s . '.home');

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
				$l . '.title',
				$l . '.image',
				$e . '.id AS extension_id'
			]);

		// Get records
		$rows = $query
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		//$preview = $this->config->get('template_positions_display');

		return $rows;
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/themes
	 * @apiParameter {
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @return Response
	 */
	public function create(Request $request)
	{
		$request->validate([
			'title' => 'required',
			'template' => 'required'
		]);

		$row = new Theme($request->all());

		if (!$row->save())
		{
			throw new \Exception($row->getError(), 409);
		}

		return $row;
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/themes/{id}
	 * @apiParameter {
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
		$row = Theme::findOrFail((int)$id);

		$row->api = route('api.themes.read', ['id' => $row->id]);

		// Permissions check
		//$item->canCreate = false;
		$row->canEdit   = false;
		$row->canDelete = false;

		if (auth()->user())
		{
			//$item->canCreate = auth()->user()->can('create themes');
			$row->canEdit   = auth()->user()->can('edit themes');
			$row->canDelete = auth()->user()->can('delete themes');
		}

		return $row;
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/themes/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "title",
	 * 		"description":   "Menu title",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "A description of the menu",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "client_id",
	 * 		"description":   "Client (admin = 1|site = 0) ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "menutype",
	 * 		"description":   "A short alias for the menu. If none provided, one will be generated from the title.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       ""
	 * }
	 * @param   Request $request
	 * @return  Response
	 */
	public function update(Request $request, $id)
	{
		$request->validate([
			'title' => 'required',
			'position' => 'required'
		]);

		$row = Theme::findOrFail($id);
		$row->fill($request->all());

		if (!$row->save())
		{
			throw new \Exception($row->getError(), 409);
		}

		return $row;
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/themes/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Theme::findOrFail($id);

		if (!$row->delete())
		{
			throw new \Exception(trans('global.messages.delete failed', ['id' => $id]), 409);
		}

		return response()->json(null, 204);
	}
}
