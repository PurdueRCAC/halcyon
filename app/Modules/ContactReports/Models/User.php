<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;

/**
 * Model for contact report user
 */
class User extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'contactreportusers';

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
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
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'contactreportid' => 'required|integer',
		'userid' => 'required|integer'
	);

	/**
	 * Defines a relationship to report
	 *
	 * @return  object
	 */
	public function report()
	{
		return $this->belongsTo(Report::class, 'contactreportid');
	}

	/**
	 * Defines a relationship to user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Defines a relationship to followers
	 *
	 * @return  object
	 */
	public function followers()
	{
		return $this->hasMany(Follow::class, 'targetuserid')->whereIsContactFollower();
	}

	/**
	 * Was the user notified?
	 *
	 * @return  bool
	 */
	public function notified()
	{
		return !is_null($this->datetimelastnotify);
	}
}
