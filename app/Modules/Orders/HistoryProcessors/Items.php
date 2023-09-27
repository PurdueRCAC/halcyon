<?php
namespace App\Modules\Orders\HistoryProcessors;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\History;
use App\Modules\Orders\Models\Item;
use Closure;

/**
 * Process Item changes
 */
class Items
{
	/**
	 * Handle content
	 *
	 * @param  History $action
	 * @param  Model   $model
	 * @return History
	 */
	public function __invoke(History $action, Model $model = null): History
	{
		if ($action->historable_type == Item::class)
		{
			if ($action->action == 'created')
			{
				$did = '<span class="text-info">added</span> an item to the order';
			}
			elseif ($action->action == 'updated')
			{
				// Get all the fields that changed
				// But filter out datetime fields
				$fields = is_object($action->new) ? array_keys(get_object_vars($action->new)) : array_keys($action->new);

				foreach ($fields as $i => $k)
				{
					if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
					{
						unset($fields[$i]);
					}
				}

				$did = '<span class="text-info">edited</span> an item';
				$did .= ', changed: ' . implode(', ', $fields);

				if (in_array('datetimefulfilled', $fields))
				{
					$acct = 'item #' . $action->historable_id;
					foreach ($model->items as $item)
					{
						if ($item->id == $action->historable_id)
						{
							$acct = '"' . $item->product->name . '"';
						}
					}
					$did = '<span class="text-success">fulfilled</span> item ' . $acct;
				}
			}
			elseif ($action->action == 'deleted')
			{
				$did = '<span class="text-danger">removed</span> an item';
			}

			$action->summary = $action->actor . ' ' . $did;
		}

		return $action;
	}
}
