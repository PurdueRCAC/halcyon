<?php

namespace App\Modules\History\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\Users\Models\User;

class History extends Model
{
	use ErrorBag, Validatable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'history';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'exit'
	];

	//protected $appends = ['href'];

	protected $casts = [
		'old' => 'object',
		'new' => 'object',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'action' => 'required'
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
	 * Historable relationship
	 *
	 * @return  object
	 */
	public function historable(): MorphTo
	{
		return $this->morphTo();
	}

	/**
	 * User relationship
	 *
	 * @return  object
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
}
