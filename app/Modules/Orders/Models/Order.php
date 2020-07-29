<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;

/**
 * Model for an order
 */
class Order extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'orders';

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	public $dates = array(
		'datetimecreated'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimecreated';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function items()
	{
		return $this->hasMany(Item::class, 'orderid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function accounts()
	{
		return $this->hasMany(Account::class, 'orderid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Defines a relationship to updates
	 *
	 * @return  object
	 */
	public function submitter()
	{
		return $this->belongsTo(User::class, 'submitteruserid');
	}

	/**
	 * Get order total
	 *
	 * @return  integer
	 */
	public function getTotalAttribute()
	{
		$ordertotal = 0;

		foreach ($this->items as $item)
		{
			$ordertotal += $item->price;
		}

		//return number_format($ordertotal / 100, 2);
		return $ordertotal;
	}

	/**
	 * Get order status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		if ($state = $this->getAttribute('state'))
		{
			switch ($state)
			{
				case 1:
					return 'pending_fulfillment';
				break;
				case 2:
					return 'pending_boassignment';
				break;
				case 3:
					return 'pending_payment';
				break;
				case 4:
					return 'pending_approval';
				break;
				case 5:
					return 'pending_collection';
				break;
				case 6:
					return 'complete';
				break;
				case 7:
					return 'canceled';
				break;
			}
		}

		//if ($this->trashed())
		if ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00')
		{
			$status = 'canceled';
		}
		else
		{
			$its = $this->items;

			$items = count($its);
			$itemsfulfilled = 0;
			$ordertotal = 0;

			foreach ($its as $item)
			{
				if ($item->isFulfilled())
				{
					$itemsfulfilled++;
				}

				$ordertotal += $item->price;
			}

			$acc = $this->accounts;

			$accountspaid     = 0;
			$accountsapproved = 0;
			$accountsassigned = 0;
			$accountsdenied   = 0;
			$amountassigned   = 0;

			$accounts = count($acc);

			foreach ($acc as $account)
			{
				if ($account->approveruserid)
				{
					$accountsassigned++;
				}

				if ($account->isApproved())
				{
					$accountsapproved++;
				}

				if ($account->isPaid())
				{
					$accountspaid++;
				}

				if ($account->isDenied())
				{
					$accountsdenied++;
				}

				$amountassigned += $account->amount;
			}

			if (($accounts == 0 && $ordertotal > 0)
			 || $amountassigned <> $ordertotal
			 || ($accountsdenied > 0 && ($accountsdenied + $accountsapproved) == $accounts))
			{
				$status = 'pending_payment';
			}
			elseif ($accountsassigned < $accounts)
			{
				$status = 'pending_boassignment';
			}
			elseif ($accountsapproved < $accounts)
			{
				$status = 'pending_approval';
			}
			elseif ($accountsapproved == $accounts && $itemsfulfilled < $items)
			{
				$status = 'pending_fulfillment';
			}
			elseif ($itemsfulfilled == $items && $accountspaid < $accounts)
			{
				$status = 'pending_collection';
			}
			else
			{
				$status = 'complete';
			}
		}

		return $status;
	}
}
