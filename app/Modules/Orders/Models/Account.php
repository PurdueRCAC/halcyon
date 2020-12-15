<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Model for an order purchase account
 */
class Account extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'orderpurchaseaccounts';

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
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved',
		'datetimeapproved',
		'datetimedenied',
		'datetimepaid',
		'datetimepaymentdoc'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * If item is trashed
	 *
	 * @return  bool
	 **/
	public function isTrashed()
	{
		return ($this->datetimeremoved
			&& $this->datetimeremoved != '0000-00-00 00:00:00'
			&& $this->datetimeremoved != '-0001-11-30 00:00:00'
			&& $this->datetimeremoved < Carbon::now()->toDateTimeString());
	}

	/**
	 * If account is approved
	 *
	 * @return  bool
	 **/
	public function isApproved()
	{
		return ($this->datetimeapproved && $this->datetimeapproved != '0000-00-00 00:00:00' && $this->datetimeapproved != '-0001-11-30 00:00:00');
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isPaid()
	{
		return ($this->datetimepaid && $this->datetimepaid != '0000-00-00 00:00:00' && $this->datetimepaid != '-0001-11-30 00:00:00');
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isDenied()
	{
		return ($this->datetimedenied && $this->datetimedenied != '0000-00-00 00:00:00' && $this->datetimedenied != '-0001-11-30 00:00:00');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function approver()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'approveruserid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function order()
	{
		return $this->belongsTo(Order::class, 'orderid');
	}

	/**
	 * Get account
	 *
	 * @return  string
	 */
	public function getAccountAttribute()
	{
		if ($this->purchaseio)
		{
			$account = $this->purchaseio;
		}
		else
		{
			$account = $this->purchasewbse;
		}

		return $account;
	}

	/**
	 * Format WBSE
	 *
	 * @return  string
	 */
	public function getPurchasewbseAttribute($purchasewbse)
	{
		$wbse = $purchasewbse;

		// insert periods
		if ($wbse)
		{
			$wbse = substr_replace($wbse, '.', 1, 0);
			$wbse = substr_replace($wbse, '.', 10, 0);
			$wbse = substr_replace($wbse, '.', 13, 0);
		}

		return $wbse;
	}

	/**
	 * Account status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		if ($this->isTrashed())
		{
			$status = 'deleted';
		}
		elseif ($this->isDenied())
		{
			$status = 'denied';
		}
		elseif (!$this->approveruserid)
		{
			$status = 'pending_assignment';
		}
		elseif (!$this->isApproved())
		{
			$status = 'pending_approval';
		}
		elseif (!$this->isPaid())
		{
			$status = 'pending_collection';
		}
		else
		{
			$status = 'paid';
		}

		return $status;
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getFormattedAmountAttribute()
	{
		$number = preg_replace('/[^0-9\-]/', '', $this->amount);

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
