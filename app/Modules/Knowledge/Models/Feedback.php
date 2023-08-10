<?php

namespace App\Modules\Knowledge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Knowledge\Events\FeedbackCreated;

/**
 * Model class for feedback
 *
 * @property int    $id
 * @property int    $target_id
 * @property string $ip
 * @property string $type
 * @property int    $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $comments
 *
 * @property string $api
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
	 * @var array<string,int>
	 */
	protected $attributes = [
		'user_id' => 0,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'user_id' => 'integer',
		'target_id' => 'integer',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
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
	 * @return  BelongsTo
	 */
	public function submitter(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'user_id');
	}

	/**
	 * Get the target page
	 *
	 * @return  BelongsTo
	 */
	public function target(): BelongsTo
	{
		return $this->belongsTo(Associations::class, 'target_id');
	}

	/**
	 * Set IP address
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setIpAttribute($value): void
	{
		// Handle edge case
		if ($value == '::1')
		{
			$value = '127.0.0.1';
		}

		$this->attributes['ip'] = $value;
	}
}
