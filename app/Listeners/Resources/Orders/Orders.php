<?php
namespace App\Listeners\Resources\Orders;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Orders\Models\Product;

/**
 * ORders listener for Resources
 */
class Orders
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event)
	{
		$product = Product::query()
			->where('resourceid', '=', $event->getAsset()->id)
			->get()
			->first();

		if ($product)
		{
			app('translator')->addNamespace('listener.resources.orders', __DIR__ . '/lang');

			$event->addSection(
				route('site.orders.products.read', ['id' => $product->id]),
				trans('listener.resources.orders::orders.purchase access')
			);
		}
	}
}
