<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use App\Modules\Orders\Helpers\Currency;
use App\Modules\Orders\Events\AccountCreating;
use App\Modules\Orders\Events\AccountUpdating;
use Carbon\Carbon;

/**
 * Model for an order purchase account
 *
 * @property int    $id
 * @property int    $orderid
 * @property string $purchasefund
 * @property string $purchasecostcenter
 * @property string $purchaseorder
 * @property string $budgetjustification
 * @property int    $amount
 * @property int    $approveruserid
 * @property int    $paymentdocid
 * @property Carbon|string|null $datetimecreated
 * @property Carbon|string|null $datetimeremoved
 * @property Carbon|string|null $datetimeapproved
 * @property Carbon|string|null $datetimedenied
 * @property Carbon|string|null $datetimepaid
 * @property Carbon|string|null $datetimepaymentdoc
 * @property int    $notice
 * @property string $purchaseio
 * @property string $purchasewbse
 *
 * @property string $api
 */
class Account extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'orderpurchaseaccounts';

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimeapproved' => 'datetime:Y-m-d H:i:s',
		'datetimedenied' => 'datetime:Y-m-d H:i:s',
		'datetimepaid' => 'datetime:Y-m-d H:i:s',
		'datetimepaymentdoc' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The event map for the model.
	 *
	 * @var  array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => AccountCreating::class,
		'updating' => AccountUpdating::class,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
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
	 */
	public function isApproved(): bool
	{
		return (!is_null($this->datetimeapproved));
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 */
	public function isPaid(): bool
	{
		return (!is_null($this->datetimepaid));
	}

	/**
	 * If account is denied
	 *
	 * @return  bool
	 */
	public function isDenied(): bool
	{
		return (!is_null($this->datetimedenied));
	}

	/**
	 * If account is collected
	 *
	 * @return  bool
	 */
	public function isCollected(): bool
	{
		return (!is_null($this->datetimepaymentdoc));
	}

	/**
	 * Defines a relationship to approver
	 *
	 * @return  BelongsTo
	 */
	public function approver(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'approveruserid');
	}

	/**
	 * Defines a relationship to parent Order
	 *
	 * @return  BelongsTo
	 */
	public function order(): BelongsTo
	{
		return $this->belongsTo(Order::class, 'orderid');
	}

	/**
	 * Get account
	 *
	 * @return  string
	 */
	public function getAccountAttribute(): string
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
	public function getPurchasewbseAttribute($purchasewbse): string
	{
		$wbse = isset($purchasewbse) === true ? $purchasewbse : '';

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
	public function setPurchasewbseAttribute($purchasewbse): void
	{
		$this->attributes['purchasewbse'] = str_replace('.', '', (string)$purchasewbse);
	}

	/**
	 * Account status
	 *
	 * @return  string
	 */
	public function getStatusAttribute(): string
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
	 * Format amount
	 *
	 * @return  string
	 */
	public function getFormattedAmountAttribute(): string
	{
		return Currency::formatNumber($this->amount);
	}
}
