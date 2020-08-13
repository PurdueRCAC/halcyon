<?php

namespace App\Modules\Users\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Halcyon\Access\Viewlevel as Level;

/**
 * Permissions
 *
 * @apiUri    /api/users/permissions
 */
class PermissionsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/users/permissions
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
			'limit'     => config('list_limit', 20),
			'order'     => Level::$orderBy,
			'order_dir' => Level::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$val = $request->input($key);
			$val = !is_null($val) ? $val : $default;

			$filters[$key] = $val;
		}

		if (!in_array($filters['order'], ['id', 'title', 'ordering']))
		{
			$filters['order'] = Level::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Level::$orderDir;
		}

		$query = Level::query();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where('title', 'like', '%' . $filters['search'] . '%');
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$rows->each(function ($item, $key)
		{
			$item->api = route('api.users.levels.read', ['id' => $item->id]);
		});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/users/permissions
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
	public function update($module, Request $request)
	{
		$id     = $request->input('id');
		//$option = $request->input('module');

		$module = Extension::findOrFail($id);

		if (!$module || !$module->extension_id)
		{
			abort(404);
		}

		$data   = $request->input('params', array());

		// Validate the posted data.
		//$return = $module->validate($module->getForm(), $data);
		// Filter and validate the form data.
		$form   = $module->getForm();
		$data   = $form->filter($data);
		$return = $form->validate($data);

		if ($return instanceof \Exception)
		{
			return redirect()->back()->withInput()->withError($return->getMessage());
		}

		// Check the validation results.
		if ($return === false)
		{
			$errors = array();
			foreach ($form->getErrors() as $err)
			{
				if ($err instanceof \Exception)
				{
					$errors[] = $err->getMessage();
				}
				else
				{
					$errors[] = $err;
				}
			}

			return redirect()->back()->withInput()->withErrors($errors);
		}

		// Save the rules.
		if (!empty($data)
		 && isset($data['rules']))
		{
			foreach ($data['rules'] as $k => $v)
			{
				$data['rules'][$k] = array_filter($v);
			}

			$rules = new Rules($data['rules']);

			$asset = Asset::findByName($module->element);

			if (!$asset || !$asset->id)
			{
				$root = Asset::getRoot();

				$asset->name  = $module->element;
				$asset->title = $module->element;
				$asset->parent_id = $root->id;
				$asset->saveAsLastChildOf($root);
			}
			$asset->rules = (string) $rules;

			if (!$asset->save())
			{
				return response()->json(['message' => trans('global.messages.update failed')]);
			}

			// We don't need this anymore
			unset($data['rules']);
		}

		$module->params = json_encode($data);

		// Attempt to save the configuration.
		if (!$module->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')]);
		}

		return response()->json(['message' => trans('config::config.permissions saved')]);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/users/permissions/{id}
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
		$row = Asset::findByName($id);

		if ($row)
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $row->id])]);
			}
		}

		return response()->json(null, 204);
	}
}
