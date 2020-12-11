<?php
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
	 * If item is trashed
	 *
	 * @return  bool
	 **/
	public function isTrashed()
	{
		return ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00' && $this->datetimeremoved != '-0001-11-30 00:00:00');
	}

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
	 * Set user notes
	 *
	 * @return  void
	 */
	public function setUsernotesAttribute($value)
	{
		$this->attributes['usernotes'] = strip_tags($value);
	}

	/**
	 * Set staff notes
	 *
	 * @return  void
	 */
	public function setStaffnotesAttribute($value)
	{
		$this->attributes['staffnotes'] = strip_tags($value);
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
	 * Format unit price
	 *
	 * @return  string
	 */
	public function formatNumber($val)
	{
		$number = preg_replace('/[^0-9\-]/', '', $val);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
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
		if ($this->isTrashed())
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

	/**
	 * Delete entry and associated data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->accounts as $row)
		{
			$row->delete();
		}

		foreach ($this->items as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getFormattedTotalAttribute()
	{
		$number = preg_replace('/[^0-9\-]/', '', $this->total);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
	}

	/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNull($t . '.datetimeremoved')
					->orWhere($t . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNotNull($t . '.datetimeremoved')
				->where($t . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		});
	}
}
