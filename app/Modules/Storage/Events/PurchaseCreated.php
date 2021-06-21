<?php

namespace App\Modules\Storage\Events;

use App\Modules\Storage\Models\Purchase;

class PurchaseCreated
{
	/**
	 * @var Purchase
	 */
	public $purchase;

	/**
	 * Constructor
	 *
	 * @param Purchase $purchase
	 * @return void
	 */
	public function __construct(Purchase $purchase)
	{
		$this->purchase = $purchase;
	}
}
