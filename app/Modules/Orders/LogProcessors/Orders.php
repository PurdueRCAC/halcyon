<?php
namespace App\Modules\Orders\LogProcessors;

use App\Modules\Orders\Models\Order;
use App\Modules\History\Models\Log;

/**
 * Orders log processor
 */
class Orders
{
	/**
	 * @param  Log $record
	 * @return Log
	 */
	public function __invoke($record)
	{
		if ($record->classname == 'OrdersController'
		 || $record->classname == 'order')
		{
			if ($record->classmethod == 'create')
			{
				$order = Order::query()
					->where('datetimecreated', '=', $record->datetime)
					->where('submitteruserid', '=', $record->targetuserid)
					->first();

				if ($order)
				{
					$route = route('site.order.read', ['id' => $order->id]);

					$record->summary = 'Order <a href="' . $route . '">#' . $record->objectid . '</a> <span class="text-success">created</span>';
				}
				else
				{
					$record->summary = 'Order <span class="text-success">created</span>';
				}
			}

			if ($record->classmethod == 'update')
			{
				$id = $record->objectid;
				$id = $id ?: $record->targetobjectid;
				if ($id <= 0)
				{
					$id = explode('/', $record->uri);
					$id = end($id);

					$record->objectid = $id;
					$record->save();
				}

				$record->summary = 'Order <a href="' . route('site.order.read', ['id' => $id]) . '">#' . $id . '</a> <span class="text-info">updated</span>';
			}

			if ($record->classmethod == 'delete')
			{
				$id = $record->objectid;
				$id = $id ?: $record->targetobjectid;
				if ($id <= 0)
				{
					$id = explode('/', $record->uri);
					$id = end($id);

					$record->objectid = $id;
					$record->save();
				}

				$record->summary = 'Order <a href="' . route('site.order.read', ['id' => $id]) . '">#' . $id . '</a> <span class="text-danger">cancelled</span>';
			}
		}

		return $record;
	}
}
