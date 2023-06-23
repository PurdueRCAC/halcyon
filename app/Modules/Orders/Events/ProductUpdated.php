<?php

namespace App\Modules\Orders\Events;

use App\Modules\Orders\Models\Product;

class ProductUpdated
{
	/**
	 * @var Product
	 */
	public $product;

	/**
	 * Constructor
	 *
	 * @param  Product $product
	 * @return void
	 */
	public function __construct(Product $product)
	{
		$this->product = $product;
	}
}
