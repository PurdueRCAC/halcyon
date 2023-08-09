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
}
