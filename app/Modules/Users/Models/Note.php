<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Events\NoteCreated;
use App\Modules\Users\Events\NoteUpdated;
use App\Modules\Users\Events\NoteDeleted;

/**
 * User note model
 */
class Note extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'user_notes';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'user_id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'user_id' => 'positive|nonzero',
		'body'    => 'notempty'
	);

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'created_at',
		'updated_at',
		'publish_up',
		'publish_down',
		'review_time',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => NoteCreated::class,
		'updated'  => NoteUpdated::class,
		'deleted'  => NoteDeleted::class,
	];

	/**
	 * Get parent member
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Get parent category
	 *
	 * @return  object
	 */
	/*public function category()
	{
		return $this->belongsToOne('App\Modules\Users\Models\Note\Category', 'category_id');
	}*/
}
