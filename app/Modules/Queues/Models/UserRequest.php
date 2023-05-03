<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use App\Modules\Queues\Events\UserRequestCreated;
use App\Modules\Queues\Events\UserRequestUpdated;
use App\Modules\Queues\Events\UserRequestDeleted;

/**
 * Model for a user request
 *
 * @property int    $id
 * @property int    $userid
 * @property string $comment
 * @property Carbon|null $datetimecreated
 */
class UserRequest extends Model
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
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'userrequests';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => UserRequestCreated::class,
		'updated' => UserRequestUpdated::class,
		'deleted' => UserRequestDeleted::class,
	];

	/**
	 * Set the comment
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setCommentAttribute($value): void
	{
		$this->attributes['comment'] = strip_tags($value);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}
}
