<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Resources\Orders;

use App\Modules\Resources\Events\AssetDisplaying;
use App\Modules\Orders\Models\Product;

/**
 * Content listener for Resources
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
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
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
