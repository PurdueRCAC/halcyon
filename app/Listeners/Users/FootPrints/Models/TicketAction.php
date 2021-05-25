<?php

namespace App\Listeners\Users\FootPrints\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Models\User;

class TicketAction extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'ticketactions';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'ticketid'
	];

	/**
	 * Cast attributes
	 *
	 * @var array
	 */
	protected $casts = [
		'userid' => 'integer',
		'actoruserid' => 'integer',
		'datetimeaction' => 'datetime',
		'datetimesubmission' => 'datetime',
		'datetimeprior' => 'datetime',
		'datetimepriorstaff' => 'datetime',
		'datetimepriorcustomer' => 'datetime',
	];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimesubmission';

	/**
	 * Default order direction for model
	 *
	 * @var string
	 */
	public static $orderDir = 'desc';

	/**
	 * User relationship
	 *
	 * @return  object
	 */
	public function submitter(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * User relationship
	 *
	 * @return  object
	 */
	public function actor(): BelongsTo
	{
		return $this->belongsTo(User::class, 'actoruserid');
	}
}
