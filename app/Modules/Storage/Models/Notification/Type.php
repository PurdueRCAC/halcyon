<?php

namespace App\Modules\Storage\Models\Notification;

use App\Modules\Storage\Models\Notification as Notify;
use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\Timeperiod;

/**
 * Model for news type
 */
class Type extends Model
{
	use Historable;

	/**
	 * Uses timestamps
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirquotanotificationtypes';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'defaulttimeperiodid')->withDefault();
	}

	/**
	 * Defines a relationship to notifications
	 *
	 * @return  object
	 */
	public function notifications()
	{
		return $this->hasMany(Notify::class, 'storagedirquotanotificationtypeid');
	}
}
