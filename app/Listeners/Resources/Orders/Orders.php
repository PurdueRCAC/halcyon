<?php
namespace App\Listeners\Resources\Orders;

use Illuminate\Events\Dispatcher;
use App\Modules\Resources\Events\AssetDeleted;
use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Orders\Models\Product;

/**
 * Orders listener for Resources
 */
class Orders
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(AssetDisplaying::class, self::class . '@handleAssetDisplaying');
		$events->listen(AssetDeleted::class, self::class . '@handleAssetDeleted');
	}

	/**
	 * Unpublish linked products when a resource is trashed
	 *
	 * @param   AssetDeleted  $event
	 * @return  void
	 */
	public function handleAssetDeleted(AssetDeleted $event): void
	{
		Product::query()
			->where('resourceid', '=', $event->asset->id)
			->delete();
	}

	/**
	 * Find related products to be listed on a resource Asset's overview
	 *
	 * @param   AssetDisplaying  $event
	 * @return  void
	 */
	public function handleAssetDisplaying(AssetDisplaying $event): void
	{
		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return;
		}

		$product = Product::query()
			->where('resourceid', '=', $event->getAsset()->id)
			->first();

		if ($product)
		{
			app('translator')->addNamespace('listener.resources.orders', __DIR__ . '/lang');

			$event->addSection(
				route('site.orders.products') . '#' . $product->id . '_product',
				trans('listener.resources.orders::orders.purchase access')
			);
		}
	}
}
