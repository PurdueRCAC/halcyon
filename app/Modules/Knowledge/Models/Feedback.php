<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Knowledge\Events\FeedbackCreated;

/**
 * Model class for feedback
 */
class Feedback extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'kb_feedbacks';

	/**
	 * The model's default values for attributes.
	 *
	 * @var array
	 */
	protected $attributes = [
		'user_id' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'user_id' => 'integer',
		'target_id' => 'integer',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'target_id' => 'required|integer',
		'ip' => 'required|string|max:10',
		'type' => 'required|string',
		'user_id' => 'nullable|integer',
		'comments' => 'nullable|string',
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => FeedbackCreated::class,
	];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'created_at';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Get the creator of this entry
	 *
	 * @return  object
	 */
	public function submitter()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'user_id');
	}

	/**
	 * Get the target page
	 *
	 * @return  object
	 */
	public function target()
	{
		return $this->belongsTo(Associations::class, 'target_id');
	}

	/**
	 * Set IP address
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setIpAttribute($value)
	{
		// Handle edge case
		if ($value == '::1')
		{
			$value = '127.0.0.1';
		}

		$this->attributes['ip'] = $value;
	}
}
