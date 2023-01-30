<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Http\Resources\ProductResource;
use App\Modules\Orders\Http\Resources\ProductResourceCollection;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

/**
 * Products
 *
 * @apiUri    /orders/products
 */
class ProductsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /orders/products
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Order category state.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "published",
	 * 			"enum": [
	 * 				"all",
	 * 				"published",
	 * 				"trashed"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "parent",
	 * 		"description":   "Parent category ID.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
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
	 * 			"default":   "created_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"created_at"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request $request
	 * @return ProductResourcEcollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'    => $request->input('search'),
			'state'     => $request->input('state', 'published'),
			'category'  => $request->input('category', 0),
			// Paging
			'limit'     => $request->input('limit', config('list_limit', 20)),
			// Sorting
			'order'     => $request->input('order', 'id'),
			'order_dir' => $request->input('order_dir', 'desc'),
		);

		if (!in_array($filters['order'], ['id', 'name']))
		{
			$filters['order'] = Product::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Product::$orderDir;
		}

		$p = (new Product)->getTable();
		$c = (new Category)->getTable();

		$query = Product::query()
			->select($p . '.*')
			->join($c, $c . '.id', $p . '.ordercategoryid')
			->whereNull($c . '.datetimeremoved')
			->withTrashed();

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where($p . '.id', '=', $filters['search']);
			}
			else
			{
				$query->where($p . '.name', 'like', '%' . $filters['search'] . '%');
			}
		}

		if ($filters['state'] == 'published')
		{
			// Nothing here
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->onlyTrashed();
		}
		else
		{
			$query->withTrashed();
		}

		if ($filters['category'])
		{
			$query->where($p . '.ordercategoryid', '=', $filters['category']);
		}

		$rows = $query
			->orderBy($p . '.' . $filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters));

		$categories = Category::query()
			->where('parentordercategoryid', '>', 0)
			->orderBy('name', 'asc')
			->get();

		return new ProductResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /orders/products
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Product name.",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "terms",
	 * 		"description":   "Terms",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation"
	 * 		},
	 * 		"401": {
	 * 			"description": "Unauthorized"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response|ProductResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'name' => 'required|string|max:64',
			'ordercategoryid' => 'required|integer|min:1',
			'description' => 'nullable|string|max:2000',
			'mou' => 'nullable|string|max:255',
			'unit' => 'nullable|string|max:16',
			'unitprice' => 'nullable',
			'recurringtimeperiodid' => 'nullable|integer',
			'sequence' => 'nullable|integer|min:1',
			'successororderproductid' => 'nullable|integer|min:1',
			'terms' => 'nullable|string|max:2000',
			'restricteddata' => 'nullable|integer',
			'resourceid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Product();
		$row->fill($request->all());
		$row->mou = $row->mou ?: '';
		$row->terms = $row->terms ?: '';
		$row->description = $row->description ?: '';

		if ($row->ordercategoryid)
		{
			if (!$row->category)
			{
				return response()->json(['message' => 'Invalid ordercategoryid'], 415);
			}
		}
		else
		{
			$row->ordercategoryid = 1;
		}

		if ($row->resourceid)
		{
			if (!$row->resource)
			{
				return response()->json(['message' => 'Invalid resourceid'], 415);
			}
		}

		if ($row->recurringtimeperiodid)
		{
			if (!$row->timeperiod)
			{
				return response()->json(['message' => 'Invalid recurringtimeperiodid'], 415);
			}
		}

		$row->datetimecreated = Carbon::now()->toDateTimeString();

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.create failed')], 500);
		}

		return new ProductResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /orders/products/{id}
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
	 * 			"description": "Successful entry read"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int $id
	 * @return ProductResource
	 */
	public function read($id)
	{
		$row = Product::findOrFail($id);

		return new ProductResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /orders/products/{id}
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
	 * 		"name":          "name",
	 * 		"description":   "Product name.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "terms",
	 * 		"description":   "Terms",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   int  $id
	 * @param   Request $request
	 * @return  Response|ProductResource
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'name' => 'nullable|string|max:64',
			'ordercategoryid' => 'nullable|integer|min:1',
			'description' => 'nullable|string|max:2000',
			'mou' => 'nullable|string|max:255',
			'unit' => 'nullable|string|max:16',
			'unitprice' => 'nullable',
			'recurringtimeperiodid' => 'nullable|integer',
			'sequence' => 'nullable|integer|min:1',
			'successororderproductid' => 'nullable|integer|min:1',
			'terms' => 'nullable|string|max:2000',
			'restricteddata' => 'nullable|integer',
			'resourceid' => 'nullable|integer',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Product::findOrFail($id);
		$row->fill($request->all());
		$row->mou = $row->mou ?: '';
		$row->terms = $row->terms ?: '';
		$row->description = $row->description ?: '';

		if ($row->ordercategoryid != $row->getOriginal('ordercategoryid'))
		{
			if (!$row->category)
			{
				return response()->json(['message' => 'Invalid ordercategoryid'], 415);
			}
		}

		if ($row->resourceid != $row->getOriginal('resourceid'))
		{
			if (!$row->resource)
			{
				return response()->json(['message' => 'Invalid resourceid'], 415);
			}
		}

		if ($row->recurringtimeperiodid != $row->getOriginal('recurringtimeperiodid'))
		{
			if (!$row->timeperiod)
			{
				return response()->json(['message' => 'Invalid recurringtimeperiodid'], 415);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.update failed')], 500);
		}

		return new ProductResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /orders/products/{id}
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
	 * @return  Response
	 */
	public function delete($id)
	{
		$row = Product::findOrFail($id);

		if (!$row->trashed())
		{
			if (!$row->delete())
			{
				return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
			}
		}

		return response()->json(null, 204);
	}
}
