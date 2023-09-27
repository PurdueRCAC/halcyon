<?php
namespace App\Modules\Orders\HistoryProcessors;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Models\History;
use App\Modules\Orders\Models\Account;
use Closure;

/**
 * Process Account changes
 */
class Accounts
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
		if ($action->historable_type == Account::class)
		{
			if ($action->action == 'created')
			{
				$acct = '#' . $action->historable_id;
				foreach ($model->accounts as $account)
				{
					if ($account->id == $action->historable_id)
					{
						$acct = $account->purchaseio ? $account->purchaseio : $account->purchasewbse;
					}
				}
				$did = '<span class="text-info">added</span> payment account ' . $acct;
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

				$did = '<span class="text-info">edited</span> ';
				$changes = array();
				$acct = '#' . $action->historable_id;
				foreach ($model->accounts as $account)
				{
					if ($account->id == $action->historable_id)
					{
						$acct = $account->purchaseio ? $account->purchaseio : $account->purchasewbse;
						if (in_array('amount', $fields))
						{
							$changes[] = 'amount';
						}
						if (in_array('budgetjustification', $fields))
						{
							$changes[] = 'budget justification';
						}
						if (in_array('approveruserid', $fields))
						{
							$changes[] = 'approver';
						}
					}
				}
				$did .= implode(', ', $changes) . ' on payment account ' . $acct;

				if (in_array('datetimeapproved', $fields))
				{
					$acct = '#' . $action->historable_id;
					foreach ($model->accounts as $account)
					{
						if ($account->id == $action->historable_id)
						{
							$acct = $account->purchaseio ? $account->purchaseio : $account->purchasewbse;
						}
					}
					$did = '<span class="text-success">approved</span> account ' . $acct;
				}
				if (in_array('approveruserid', $fields))
				{
					$acct = '#' . $action->historable_id;
					foreach ($model->accounts as $account)
					{
						if ($account->id == $action->historable_id)
						{
							$acct = $account->purchaseio ? $account->purchaseio : $account->purchasewbse;
							$acct .= ' to ' . ($account->approver ? $account->approver->name : trans('global.none'));
						}
					}
					$did = '<span class="text-info">set</span> approver for account ' . $acct;
				}
				if (in_array('datetimedenied', $fields))
				{
					$did = '<span class="text-danger">denied</span> account #' . $action->historable_id;
				}
				if (in_array('notice', $fields) && count($fields) == 1)
				{
					$acct = '#' . $action->historable_id;
					foreach ($model->accounts as $account)
					{
						if ($account->id == $action->historable_id)
						{
							$acct = $account->purchaseio ? $account->purchaseio : $account->purchasewbse;
						}
					}
					$did = '<span class="text-info">set</span> reminder on account ' . $acct;
				}
			}
			elseif ($action->action == 'deleted')
			{
				$did = '<span class="text-danger">removed</span> a payment account';
			}

			$action->summary = $action->actor . ' ' . $did;
		}

		return $action;
	}
}
