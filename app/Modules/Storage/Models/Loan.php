<?php

namespace App\Modules\Storage\Models;

use App\Modules\Groups\Models\Group;
use App\Modules\Storage\Events\LoanCreated;

/**
 * Storage model for a resource directory
 */
class Loan extends Purchase
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirloans';

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => LoanCreated::class,
	];

	/**
	 * Defines a relationship to a lender group
	 *
	 * @return  object
	 */
	public function seller()
	{
		return $this->belongsTo(Group::class, 'lendergroupid');
	}

	/**
	 * Defines a relationship to a lender group
	 *
	 * @return  object
	 */
	public function lender()
	{
		return $this->belongsTo(Group::class, 'lendergroupid');
	}

	/**
	 * Get counter entry
	 *
	 * @return  Loan|null
	 */
	public function getCounterAttribute()
	{
		return self::query()
			->where('datetimestart', '=', $this->datetimestart)
			->where('datetimestop', '=', ($this->hasEnd() ? $this->datetimestop : null))
			->where('groupid', '=', $this->lendergroupid)
			->where('lendergroupid', '=', $this->groupid)
			->get()
			->first();
	}

	/**
	 * Get the transaction type (loan|purchase)
	 *
	 * @return  string
	 */
	public function getTypeAttribute()
	{
		return 'loan';
	}
}
