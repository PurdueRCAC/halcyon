<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Queues\Events\UserRequestCreated;
use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Queues\Events\UserRequestDeleted;

/**
 * Model for a user request
 */
class UserRequest extends Model
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
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'userrequests';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'userid' => 'required|integer|min:1'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created' => UserRequestCreated::class,
		'updated' => UserRequestUpdated::class,
		'deleted' => UserRequestDeleted::class,
	];

	/**
	 * Get parent group
	 *
	 * @return  object
	 */
	public function setCommentAttribute($value)
	{
		$this->attributes['comment'] = strip_tags($value);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}
}
