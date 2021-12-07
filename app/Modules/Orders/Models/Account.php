<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Orders\Helpers\Currency;
use Carbon\Carbon;

/**
 * Model for an order purchase account
 */
class Account extends Model
{
	use Historable, SoftDeletes;

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
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

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
	 * If account is approved
	 *
	 * @return  bool
	 **/
	public function isApproved()
	{
		return (!is_null($this->datetimeapproved));
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isPaid()
	{
		return (!is_null($this->datetimepaid));
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isDenied()
	{
		return (!is_null($this->datetimedenied));
	}

	/**
	 * If account is documented
	 *
	 * @return  bool
	 **/
	public function isCollected()
	{
		return (!is_null($this->datetimepaymentdoc));
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
	 * @param   string  $purchasewbse
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
	 * Format WBSE
	 *
	 * @param   string  $purchasewbse
	 * @return  void
	 */
	public function setPurchasewbseAttribute($purchasewbse)
	{
		$this->attributes['purchasewbse'] = str_replace('.', '', (string)$purchasewbse);
	}

	/**
	 * Account status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		if ($this->trashed())
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
		return Currency::formatNumber($this->amount);
	}
}
