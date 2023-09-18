<?php

namespace App\Modules\History\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Models\User;
use App\Modules\History\Helpers\Diff\Formatter\Table;
use App\Modules\History\Helpers\Diff;

/**
 * Model for logging changes to objects
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $historable_id
 * @property string $historable_type
 * @property string $historable_table
 * @property string $action
 * @property object $old
 * @property object $new
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class History extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'history';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'exit'
	];

	/**
	 * Cast attributes
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'old' => 'object',
		'new' => 'object',
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
	 * Processors that will process all log records
	 *
	 * To process records of a single handler instead, add the processor on that specific handler
	 *
	 * @var callable[]
	 */
	protected static $processors = array();

	/**
	 * Historable relationship
	 *
	 * @return  MorphTo
	 */
	public function historable(): MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * User relationship
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class);
	}

	/**
	 * Get edit URL
	 *
	 * @return  string|null
	 */
	public function getHrefAttribute(): ?string
	{
		if ($this->historable)
		{
			return $this->historable->editUrl();
		}

		return null;
	}

	/**
	 * Get historable type
	 *
	 * @return  string|null
	 */
	public function getTypeAttribute(): ?string
	{
		if (preg_match('/^App\\\Modules\\\([a-zA-Z\-_]+)\\\Models\\\([a-zA-Z\-_]+)$/i', $this->attributes['historable_type'], $matches))
		{
			return $matches[1] . ' - ' . $matches[2];
		}

		return $this->attributes['historable_type'];
	}

	/**
	 * Get actor's name
	 *
	 * @return  string
	 */
	public function getActorAttribute(): ?string
	{
		$actor = trans('global.unknown');

		if (!$this->user_id)
		{
			$actor = trans('global.system user');
		}

		if ($this->user)
		{
			$actor = e($this->user->name);
		}

		return $actor;
	}

	/**
	 * Get a diff of the specified column
	 *
	 * @param   string  $column
	 * @return  string
	 */
	public function diff($column): string
	{
		if (!isset($this->old->{$column})
		 && !isset($this->new->{$column}))
		{
			return '';
		}

		$ota = $this->old->{$column};
		$nta = $this->new->{$column};

		if (is_string($ota))
		{
			$ota = explode("\n", $nta);
		}
		if (is_string($ota))
		{
			$nta = explode("\n", $nta);
		}

		$formatter = new Table();

		return $formatter->format(new Diff($ota, $nta));
	}

	/**
	 * Adds a processor on to the stack.
	 *
	 * @param callable $callback
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
	 * Process a History entry
	 *
	 * @param  History $record
	 * @param  Model|null $model
	 * @return History
	 */
	public function process($record, Model $model = null): History
	{
		foreach (self::$processors as $processor)
		{
			$record = $processor($record, $model);

			if ($record->summary)
			{
				break;
			}
		}

		return $record;
	}
}
