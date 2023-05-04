<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Model for following users and groups
 *
 * @property int    $id
 * @property int    $userid
 * @property int    $targetuserid
 * @property int    $membertype
 * @property Carbon|null $datecreated
 * @property Carbon|null $dateremoved
 * @property Carbon|null $datelastseen
 */
class Follow extends Model
{
	use Historable;

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
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'linkusers';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var string
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
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$model->membertype = $model->membertype
				? $model->membertype
				: 10;
		});
	}

	/**
	 * Defines a relationship to user following something
	 *
	 * @return  BelongsTo
	 */
	public function follower(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to object being followed
	 *
	 * @return  BelongsTo
	 */
	public function following(): BelongsTo
	{
		if ($this->groupid)
		{
			return $this->belongsTo('App\Modules\Groups\Models\Group', 'groupid');
		}
		return $this->belongsTo('App\Modules\Users\Models\User', 'targetuserid');
	}

	/**
	 * Define a query scope
	 *
	 * @param  object $query
	 * @return object
	 */
	public function scopeWhereIsContactFollower($query)
	{
		return $query->where('membertype', '=', 10);
	}
}
