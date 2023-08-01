<?php

namespace App\Modules\Orders\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Modules\Orders\Models\Product;
use App\Modules\Orders\Http\Resources\CartResource;

/**
 * Shopping Cart
 *
 * @apiUri    /orders/cart
 */
class CartController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /orders/cart
	 * @apiAuthorization  true
	 * @param  Request $request
	 * @return CartResource
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
	 * @apiUri    /orders/cart
	 * @apiAuthorization  true
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
	 * @return JsonResponse|CartResource
	 */
	public function create(Request $request)
	{
		$rules = [
			'productid' => 'required|integer|min:1',
			//'userid' => 'required|integer|min:1',
			'quantity' => 'required|integer|min:1',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
	 * @apiUri    /orders/cart/{id}
	 * @apiAuthorization  true
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
	 * @return CartResource
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
	 * @apiUri    /orders/cart/{id}
	 * @apiAuthorization  true
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
	 * @return  JsonResponse|CartResource
	 */
	public function update($id, Request $request)
	{
		$rules = [
			'quantity' => 'required|integer|min:1',
			'price'    => 'nullable|integer'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

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
	 * @apiUri    /orders/cart/{id}
	 * @apiAuthorization  true
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
	 * @return  CartResource
	 */
	public function delete($id)
	{
		$cart = app('cart');
		$cart->restore(auth()->user()->username);
		$cart->remove(
			$id
		);
		$cart->store(auth()->user()->username);

		return new CartResource($cart);
	}
}
