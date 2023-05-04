<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;

/**
 * Model for contact report user
 *
 * @property int $id
 * @property int $contactreportid
 * @property int $userid
 * @property Carbon|null $datetimecreated
 * @property Carbon|null $datetimelastnotify
 */
class User extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'contactreportusers';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimelastnotify' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * Defines a relationship to report
	 *
	 * @return  BelongsTo
	 */
	public function report(): BelongsTo
	{
		return $this->belongsTo(Report::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to followers
	 *
	 * @return  HasMany
	 */
	public function followers(): HasMany
	{
		return $this->hasMany(Follow::class, 'targetuserid')->whereIsContactFollower();
	}

	/**
	 * Was the user notified?
	 *
	 * @return  bool
	 */
	public function notified(): bool
	{
		return !is_null($this->datetimelastnotify);
	}
}
