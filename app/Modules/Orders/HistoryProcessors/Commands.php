<?php
namespace App\Modules\Orders\HistoryProcessors;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\History;
use App\Modules\Users\Models\User;
use Closure;

/**
 * Process system changes
 */
class Commands
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
		if ($action->historable_type == 'orders:emailstatus')
		{
			$persons = array();

			if (isset($action->new->targetuserid) && $action->new->targetuserid)
			{
				$person = User::find($action->new->targetuserid);
			}
			elseif (isset($action->new->uri))
			{
				$person = User::findByEmail($action->new->uri);
			}

			$persons[] = ($person ? $person->name . ' (' . $person->username . ')' : $action->new->uri);

			if ($action->multiples)
			{
				foreach ($action->multiples as $act)
				{
					if (isset($act->new->targetuserid) && $act->new->targetuserid)
					{
						$person = User::find($act->new->targetuserid);
					}
					elseif (isset($act->new->uri))
					{
						$person = User::findByEmail($act->new->uri);
					}

					$persons[] = ($person ? $person->name . ' (' . $person->username . ')' : $act->new->uri);
				}
			}

			$did  = '<span class="text-info">emailed</span> order status (<a href="#list' . $action->id . '" data-toggle="collapse" data-parent="#action_' . $action->id . '">recipients</a>)';
			$did .= '<ul id="list' . $action->id . '" class="collapse">';
			$did .= '<li>' . implode('</li><li>', $persons) . '</li>';
			$did .= '</ul>';

			$action->summary = $action->actor . ' ' . $did;
		}

		return $action;
	}
}
