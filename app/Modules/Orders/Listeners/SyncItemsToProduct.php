<?php

namespace App\Modules\Orders\Listeners;

use Illuminate\Events\Dispatcher;
use App\Modules\Orders\Events\ProductUpdated;
use App\Modules\Orders\Models\Item;

/**
 * Sync order items when a product is changed
 */
class SyncItemsToProduct
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(ProductUpdated::class, self::class . '@handleProductUpdated');
	}

	/**
	 * Sync items
	 *
	 * @param   ProductUpdated  $event
	 * @return  void
	 */
	public function handleProductUpdated(ProductUpdated $event): void
	{
		$product = $event->product;

		// Check if the recurring setting has changed
		if ($product->getOriginal('recurringtimeperiodid') == $product->recurringtimeperiodid)
		{
			return;
		}

		// If the product is changed to be recurring...
		if (!$product->getOriginal('recurringtimeperiodid') && $product->recurringtimeperiodid)
		{
			$items = Item::query()
				->where('orderproductid', '=', $product->id)
				->where('origorderitemid', '=', 0)
				->get();

			foreach ($items as $item)
			{
				$item->origorderitemid = $item->id;
				$item->saveQuietly();
			}
		}

		// If the product is changed to no longer be recurring...
		if ($product->getOriginal('recurringtimeperiodid') && !$product->recurringtimeperiodid)
		{
			$items = Item::query()
				->where('orderproductid', '=', $product->id)
				->where('origorderitemid', '!=', 0)
				->get();

			foreach ($items as $item)
			{
				$item->origorderitemid = 0;
				$item->saveQuietly();
			}
		}
	}
}
