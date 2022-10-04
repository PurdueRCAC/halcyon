<?php

namespace App\Modules\History\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\Users\Models\User;

class Log extends Model
{
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
	 * Target user relationship
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
		$value = strtoupper($value);
		if (!in_array($value, ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'CONNECT', 'OPTIONS', 'TRACE']))
		{
			$value = 'GET';
		}
		$this->attributes['transportmethod'] = $value;
	}

	/**
	 * Set servername
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setServernameAttribute($value)
	{
		$this->attributes['servername'] = Str::limit($value, 128, '');
	}

	/**
	 * Set IP
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setIpAttribute($value)
	{
		$value = $value == 'localhost' ? '127.0.0.1' : $value;
		$value = $value == '::1' ? '127.0.0.1' : $value;

		$this->attributes['ip'] = Str::limit($value, 39, '');
	}

	/**
	 * Set uri
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setUriAttribute($value)
	{
		$this->attributes['uri'] = Str::limit($value, 128, '');
	}

	/**
	 * Set app
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setAppAttribute($value)
	{
		$this->attributes['app'] = Str::limit($value, 20, '');
	}

	/**
	 * Set classname
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setClassnameAttribute($value)
	{
		$this->attributes['classname'] = Str::limit($value, 32, '');
	}

	/**
	 * Set classmethod
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setClassmethodAttribute($value)
	{
		$this->attributes['classmethod'] = Str::limit($value, 16, '');
	}

	/**
	 * Set payload
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setPayloadAttribute($value)
	{
		$this->attributes['payload'] = Str::limit($value, 2000, '');
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

	/**
	 * Convert to a History item
	 *
	 * @return History
	 */
	public function toHistory()
	{
		$item = new History;
		$item->created_at = $this->datetime;
		if ($this->transportmethod == 'POST')
		{
			$item->action = 'created';
		}
		if ($this->transportmethod == 'PUT')
		{
			$item->action = 'updated';
		}
		if ($this->transportmethod == 'DELETE')
		{
			$item->action = 'deleted';
		}
		$item->user_id = $this->userid;
		$item->historable_id = $this->targetobjectid;
		$item->historable_type = $this->classname;

		$item->old = [];
		$item->new = $this->toArray();

		return $item;
	}
}
