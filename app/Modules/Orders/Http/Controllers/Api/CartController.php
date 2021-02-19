<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Http\Resources\CartResource;
use App\Modules\Orders\Http\Resources\CartResourceCollection;

/**
 * Products
 *
 * @apiUri    /api/orders/cart
 */
class CartController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/cart
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "state",
	 * 		"description":   "Order category state.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "published",
	 * 		"allowedValues": "all, published, trashed"
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "parent",
	 * 		"description":   "Parent category ID.",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
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
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"type":          "integer",
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
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       1
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       "name",
	 * 		"allowedValues": "id, created_at"
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
		$cart = app('cart');
		$cart->restore(auth()->user()->username);

		return new CartResource($cart);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /api/orders/cart
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "name",
	 * 		"description":   "Product name.",
	 * 		"type":          "string",
	 * 		"required":      true,
	 * 		"default":       ""
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"type":          "integer",
	 * 		"required":      true,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       0
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
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
			'productid' => 'required|integer|min:1',
			//'userid' => 'required|integer|min:1',
			'quantity' => 'required|integer|min:1',
		]);

		$product = Product::find($request->input('productid'));

		if (!$product)
		{
			return response()->json(['message' => 'Invalid productid'], 415);
		}

		$cart = app('cart');
		$cart->restore(auth()->user()->username);
		$cart->add(
			$product->id,
			$product->name,
			$request->input('quantity'),
			$product->decimalUnitprice
		);
		$cart->store(auth()->user()->username);

		return new CartResource($cart);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /api/orders/cart/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param  integer $id
	 * @return ProductResource
	 */
	public function read($id)
	{
		$cart = app('cart');
		$cart->restore(auth()->user()->username);

		$row = $cart->get($id);

		return new CartResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /api/orders/cart/{id}
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
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "description",
	 * 		"description":   "Longer description of the category",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "mou",
	 * 		"description":   "Memorandum of Undertsanding",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unit",
	 * 		"description":   "Product unit",
	 * 		"type":          "string",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "unitprice",
	 * 		"description":   "Price per unit",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "sequence",
	 * 		"description":   "Product order",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "ordercategoryid",
	 * 		"description":   "Category ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "recurringtimeperiodid",
	 * 		"description":   "Recurring time period ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "restricteddata",
	 * 		"description":   "Restricted data",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "resourceid",
	 * 		"description":   "Resource ID",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"default":       null
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
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
			'quantity' => 'required|integer|min:1',
			'price'    => 'nullable|integer'
		]);

		$cart = app('cart');
		$cart->restore(auth()->user()->username);
		$cart->update(
			$id,
			$request->input('quantity')
		);
		$cart->store(auth()->user()->username);

		return new CartResource($cart);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /api/orders/cart/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @param   integer  $id
	 * @return  Response
	 */
	public function delete($id)
	{
		$cart = app('cart');
		$cart->restore(auth()->user()->username);
		$cart->remove(
			$id
		);
		$cart->store(auth()->user()->username);

		/*if (!count($cart->content()))
		{
			$cart->forget(auth()->user()->username);
		}*/

		return new CartResource($cart);
	}
}
