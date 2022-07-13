<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * Model for following users and groups
 */
class Follow extends Model
{
	use ErrorBag, Validatable, Historable;

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
	 * @var string
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
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'targetuserid' => 'required|integer',
		'userid' => 'required|integer'
	);

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
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
	 * @return  object User
	 */
	public function follower()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to object being followed
	 *
	 * @return  object User|Group
	 */
	public function following()
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
