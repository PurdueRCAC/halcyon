<?php

namespace App\Modules\Storage\Models\Notification;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Storage\Models\Notification as Notify;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\Timeperiod;

/**
 * Model for notification type
 *
 * @property int    $id
 * @property string $name
 * @property int    $defaulttimeperiodid
 * @property int    $valuetype
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
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function timeperiod(): BelongsTo
	{
		return $this->belongsTo(Timeperiod::class, 'defaulttimeperiodid')->withDefault();
	}

	/**
	 * Defines a relationship to notifications
	 *
	 * @return  HasMany
	 */
	public function notifications(): HasMany
	{
		return $this->hasMany(Notify::class, 'storagedirquotanotificationtypeid');
	}
}
