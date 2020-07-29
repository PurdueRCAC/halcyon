<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Category;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Http\Resources\ProductResource;
use App\Modules\Orders\Http\Resources\ProductResourceCollection;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

class ProductsController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /orders/products
	 * @apiParameter {
	 * 		"name":          "state",
	 * 		"description":   "Order category state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "published"
	 * 		"allowedValues": "all, published, trashed"
	 * }
	 * @apiParameter {
	 * 		"name":          "parent",
	 * 		"description":   "Parent category ID.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
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
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, created_at"
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "asc",
	 * 		"allowedValues": "asc, desc"
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
			->where($c . '.datetimeremoved', '=', '0000-00-00 00:00:00');

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
			$query->where($p . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		}
		elseif ($filters['state'] == 'trashed')
		{
			$query->withTrashed()->where($p . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
			//$query->onlyTrashed();
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
			//->where('datetimeremoved', '=', '0000-00-00 00:00:00')
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
	 * 		"name":          "name",
	 * 		"description":   "Product name.",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"name":          "terms",
	 * 		"description":   "Terms",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param  Request  $request
	 * @return ProductResource
	 */
	public function create(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:64',
			'ordercategoryid' => 'required|integer|min:1',
			'description' => 'nullable|string|max:2000',
			'mou' => 'nullable|string|max:255',
			'unit' => 'nullable|string|max:16',
			'unitprice' => 'nullable|integer',
			'recurringtimeperiodid' => 'nullable|integer',
			'sequence' => 'nullable|integer|min:1',
			'successororderproductid' => 'nullable|integer|min:1',
			'terms' => 'nullable|string|max:2000',
			'restricteddata' => 'nullable|integer',
			'resourceid' => 'nullable|integer|min:1',
		]);

		$row = new Product();
		$row->fill($request->all());

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
			return response()->json(['message' => trans('messages.create failed')], 500);
		}

		return new ProductResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /orders/products/{id}
	 * @apiParameter {
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @param  integer $id
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
	 * 		"name":          "name",
	 * 		"description":   "Product name.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"name":          "terms",
	 * 		"description":   "Terms",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @param   integer  $id
	 * @param   Request $request
	 * @return  ProductResource
	 */
	public function update($id, Request $request)
	{
		$request->validate([
			'name' => 'nullable|string|max:64',
			'ordercategoryid' => 'nullable|integer|min:1',
			'description' => 'nullable|string|max:2000',
			'mou' => 'nullable|string|max:255',
			'unit' => 'nullable|string|max:16',
			'unitprice' => 'nullable|integer',
			'recurringtimeperiodid' => 'nullable|integer',
			'sequence' => 'nullable|integer|min:1',
			'successororderproductid' => 'nullable|integer|min:1',
			'terms' => 'nullable|string|max:2000',
			'restricteddata' => 'nullable|integer',
			'resourceid' => 'nullable|integer|min:1',
		]);

		$row = Product::findOrFail($id);
		$row->fill($request->all());

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
			return response()->json(['message' => trans('messages.update failed')], 500);
		}

		return new ProductResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /orders/products/{id}
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
	public function destroy($id)
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
