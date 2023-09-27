<?php
namespace App\Modules\Orders\HistoryProcessors;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\History;
use App\Modules\Orders\Models\Order;
use Closure;

/**
 * Process Item changes
 */
class Orders
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
		if ($action->historable_type == Order::class)
		{
			if ($action->action == 'created')
			{
				$did = '<span class="text-success">placed</span> this order';
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

				$did = '<span class="text-info">edited</span> this order';

				if (in_array('userid', $fields))
				{
					$did = '<span class="text-info">set</span> the user to ' . ($model->user ? $model->user->name : trans('global.none'));
				}
				if (in_array('groupid', $fields))
				{
					$did = '<span class="text-info">set</span> the group to ' . ($model->groupid && $model->group ? $model->group->name : trans('global.none'));
				}
				if (in_array('usernotes', $fields))
				{
					$did = '<span class="text-info">edited</span> user notes';
				}
				if (in_array('staffnotes', $fields))
				{
					$did = '<span class="text-info">edited</span> staff notes';
				}

				// Only updated notice state
				if (isset($action->new->notice) && count($fields) == 1)
				{
					$action->skip = true;
				}
			}
			elseif ($action->action == 'deleted')
			{
				$did = '<span class="text-danger">canceled</span> this order';
			}

			if (!$action->user_id && isset($action->new->notice))
			{
				$did = '<span class="text-info">emailed</span> order status';
			}

			$action->summary = $action->actor .' ' . $did;
		}

		return $action;
	}
}
