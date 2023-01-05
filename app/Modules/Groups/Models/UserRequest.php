<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UserRequestCreated;
use App\Modules\Groups\Events\UserRequestDeleted;

/**
 * Group member model
 */
class UserRequest extends Model
{
	use Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'userrequests';

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
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'userid' => 'required|integer|min:1'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created'  => UserRequestCreated::class,
		'updated'  => UserRequestUpdated::class,
		'deleted'  => UserRequestDeleted::class,
	];

	/**
	 * Set comment value
	 *
	 * @return  object
	 */
	public function setCommentAttribute(string $value)
	{
		$this->attributes['comment'] = strip_tags($value);
	}

	/**
	 * Get member
	 *
	 * @return  object
	 */
	public function member()
	{
		return $this->belongsTo(Member::class, 'userrequestid');
	}

	/**
	 * Get parent user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withTrashed();
	}
}
