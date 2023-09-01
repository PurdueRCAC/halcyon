<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Groups\Models\Group;
use App\Modules\Storage\Events\LoanCreated;
use App\Modules\Storage\Events\LoanUpdated;
use App\Modules\Storage\Events\LoanDeleted;
use Carbon\Carbon;

/**
 * Storage model for a resource directory
 *
 * @property int    $id
 * @property int    $resourceid
 * @property int    $groupid
 * @property Carbon|null $datetimestart
 * @property Carbon|null $datetimestop
 * @property int    $bytes
 * @property int    $lendergroupid
 * @property string $comment
 *
 * @property string $api
 * @property int    $loanedbytes
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
		'updated' => LoanUpdated::class,
		'deleted' => LoanDeleted::class,
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'resourceid' => 'integer',
		'groupid' => 'integer',
		'bytes' => 'integer',
		'lendergroupid' => 'integer',
		'datetimestart' => 'datetime',
		'datetimestop' => 'datetime',
	];

	/**
	 * Defines a relationship to a lender group
	 *
	 * @return  BelongsTo
	 */
	public function seller(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'lendergroupid');
	}

	/**
	 * Defines a relationship to a lender group
	 *
	 * @return  BelongsTo
	 */
	public function lender(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'lendergroupid');
	}

	/**
	 * Get counter entry
	 *
	 * @return  Loan|null
	 */
	public function getCounterAttribute(): ?Loan
	{
		return self::query()
			->where('datetimestart', '=', $this->datetimestart)
			->where('datetimestop', '=', ($this->hasEnd() ? $this->datetimestop : null))
			->where('groupid', '=', $this->lendergroupid)
			->where('lendergroupid', '=', $this->groupid)
			->first();
	}

	/**
	 * Get the transaction type (loan|purchase)
	 *
	 * @return  string
	 */
	public function getTypeAttribute(): string
	{
		return 'loan';
	}
}
