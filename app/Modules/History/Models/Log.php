<?php

namespace App\Modules\History\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\Users\Models\User;

class Log extends Model
{
	use ErrorBag, Validatable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetime';

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
	protected $table = 'log';

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
	 * @var array
	 */
	protected $rules = array(
		'transportmethod' => 'required'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for model
	 *
	 * @var string
	 */
	public static $orderDir = 'desc';

	/**
	 * User relationship
	 *
	 * @return  object
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * User relationship
	 *
	 * @return  object
	 */
	public function targetuser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'targetuserid');
	}

	/**
	 * Set transport method
	 *
	 * @param   string  $value
	 * @return  object
	 */
	public function setTransportmethodAttribute($value)
	{
		$this->attributes['transportmethod'] = strtoupper($value);
	}

	/**
	 * Get the payload as JSON
	 *
	 * @return  object
	 */
	public function getJsonPayloadAttribute()
	{
		$payload = $this->payload;

		if (substr($payload, 0, 1) != '{'
		 && substr($payload, 0, 1) != '[')
		{
			$payload = new \stdClass;
			$payload->user_agent = $this->payload;
		}
		else
		{
			$payload = json_decode($payload);

			if (json_last_error() !== JSON_ERROR_NONE)
			{
				$payload = null;
			}
		}

		if (!$payload)
		{
			$payload = new \stdClass;
		}

		return $payload;
	}
}
