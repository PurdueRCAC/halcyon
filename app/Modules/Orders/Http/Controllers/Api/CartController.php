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
	 * 		"name":          "quantity",
	 * 		"description":   "Quantity",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "price",
	 * 		"description":   "Price",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
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
	 * 		"name":          "quantity",
	 * 		"description":   "Quantity",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "price",
	 * 		"description":   "Price",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
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
