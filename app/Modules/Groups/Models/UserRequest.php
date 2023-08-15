<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\UserRequestCreated;
use App\Modules\Groups\Events\UserRequestDeleted;
use Carbon\Carbon;

/**
 * Use request model
 *
 * @property int    $id
 * @property int    $userid
 * @property string $comment
 * @property Carbon|null $datetimecreated
 *
 * @property string $api
 */
class UserRequest extends Model
{
	use Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datecreated';

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
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => UserRequestCreated::class,
		'deleted' => UserRequestDeleted::class,
	];

	/**
	 * Set comment value
	 *
	 * @param   string $value
	 * @return  void
	 */
	public function setCommentAttribute(string $value): void
	{
		$this->attributes['comment'] = strip_tags($value);
	}

	/**
	 * Get member
	 *
	 * @return  BelongsTo
	 */
	public function member(): BelongsTo
	{
		return $this->belongsTo(Member::class, 'userrequestid');
	}

	/**
	 * Get parent user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withTrashed();
	}
}
