<?php

namespace App\Modules\History\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Modules\Users\Models\User;
use stdClass;

/**
 * Access log
 *
 * @property int    $id
 * @property Carbon|null $datetime
 * @property string $ip
 * @property string $hostname
 * @property int    $userid
 * @property int    $status
 * @property string $transportmethod
 * @property string $servername
 * @property string $uri
 * @property string $app
 * @property string $classname
 * @property string $classmethod
 * @property string $objectid
 * @property string $payload
 * @property int    $groupid
 * @property int    $targetuserid
 * @property int    $targetobjectid
 *
 * @property string $summary Dynamically generated human-readable text
 */
class Log extends Model
{
	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetime';

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
	protected $table = 'log';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Processors that will process all log records
	 *
	 * To process records of a single handler instead, add the processor on that specific handler
	 *
	 * @var callable[]
	 */
	protected static $processors = array();

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
	 * Adds a processor on to the stack.
	 *
	 * @param callable[] $callback
	 * @return void
	 */
	public static function pushProcessor(callable $callback)
	{
		array_unshift(self::$processors, $callback);
	}

	/**
	 * Removes the processor on top of the stack and returns it.
	 *
	 * @throws \LogicException If empty processor stack
	 * @return callable
	 */
	public static function popProcessor(): callable
	{
		if (!self::$processors)
		{
			throw new \LogicException('You tried to pop from an empty processor stack.');
		}

		return array_shift(self::$processors);
	}

	/**
	 * Get the lsit of processors
	 *
	 * @return callable[]
	 */
	public static function getProcessors(): array
	{
		return self::$processors;
	}

	/**
	 * Process a log entry
	 *
	 * @param Log $record
	 * @return Log
	 */
	public function process($record): Log
	{
		foreach (self::$processors as $processor)
		{
			$record = $processor($record);

			if ($record->summary)
			{
				break;
			}
		}

		return $record;
	}

	/**
	 * User relationship
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Target user relationship
	 *
	 * @return  BelongsTo
	 */
	public function targetuser(): BelongsTo
	{
		return $this->belongsTo(User::class, 'targetuserid');
	}

	/**
	 * Set transport method
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setTransportmethodAttribute($value): void
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
	public function setServernameAttribute($value): void
	{
		$this->attributes['servername'] = Str::limit($value, 128, '');
	}

	/**
	 * Set IP
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setIpAttribute($value): void
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
	public function setUriAttribute($value): void
	{
		$this->attributes['uri'] = Str::limit($value, 128, '');
	}

	/**
	 * Set app
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setAppAttribute($value): void
	{
		$this->attributes['app'] = Str::limit($value, 20, '');
	}

	/**
	 * Set classname
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setClassnameAttribute($value): void
	{
		$this->attributes['classname'] = Str::limit($value, 32, '');
	}

	/**
	 * Set classmethod
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setClassmethodAttribute($value): void
	{
		$this->attributes['classmethod'] = Str::limit($value, 16, '');
	}

	/**
	 * Set payload
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setPayloadAttribute($value): void
	{
		$this->attributes['payload'] = Str::limit($value, 2000, '');
	}

	/**
	 * Get the payload as JSON
	 *
	 * @return  stdClass
	 */
	public function getJsonPayloadAttribute()
	{
		$payload = $this->payload;

		if (substr($payload, 0, 1) != '{'
		 && substr($payload, 0, 1) != '[')
		{
			$payload = new stdClass;
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
			$payload = new stdClass;
		}

		return $payload;
	}

	/**
	 * Get a payload value
	 *
	 * @param string $propertyName
	 * @param mixed $defaultValue
	 * @return mixed
	 */
	public function getExtraProperty(string $propertyName, $defaultValue = null)
	{
		if (isset($this->jsonPayload->{$propertyName}))
		{
			return $this->jsonPayload->{$propertyName};
		}
		return $defaultValue;
	}

	/**
	 * Convert to a History item
	 *
	 * @return History
	 */
	public function toHistory(): History
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
