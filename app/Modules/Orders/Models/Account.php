<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

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
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated'
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
	 * If account is approved
	 *
	 * @return  bool
	 **/
	public function isApproved()
	{
		return ($this->datetimeapproved && $this->datetimeapproved != '0000-00-00 00:00:00');
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isPaid()
	{
		return ($this->datetimepaid && $this->datetimepaid != '0000-00-00 00:00:00');
	}

	/**
	 * If account is paid
	 *
	 * @return  bool
	 **/
	public function isDenied()
	{
		return ($this->datetimedenied && $this->datetimedenied != '0000-00-00 00:00:00');
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
	 * Format WBSE
	 *
	 * @return  string
	 */
	public function getPurchasewbseAttribute($purchasewbse)
	{
		$wbse = $purchasewbse;

		// insert periods
		$wbse = substr_replace($wbse, '.', 1, 0);
		$wbse = substr_replace($wbse, '.', 10, 0);
		$wbse = substr_replace($wbse, '.', 13, 0);

		return $wbse;
	}

	/**
	 * Account status
	 *
	 * @return  string
	 */
	public function getStatusAttribute()
	{
		if ($this->datetimeremoved && $this->datetimeremoved != '0000-00-00 00:00:00')
		{
			$status = 'deleted';
		}
		elseif ($this->datetimedenied && $this->datetimedenied != '0000-00-00 00:00:00')
		{
			$status = 'denied';
		}
		elseif (!$this->approveruserid)
		{
			$status = 'pending_assignment';
		}
		elseif (!$this->datetimeapproved || $this->datetimeapproved == '0000-00-00 00:00:00')
		{
			$status = 'pending_approval';
		}
		elseif (!$this->datetimepaid || $this->datetimepaid == '0000-00-00 00:00:00')
		{
			$status = 'pending_collection';
		}
		else
		{
			$status = 'paid';
		}

		return $status;
	}
}
