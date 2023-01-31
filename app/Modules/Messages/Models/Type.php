<?php

namespace App\Modules\Messages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Asset;
use App\Modules\Messages\Events\TypeCreating;
use App\Modules\Messages\Events\TypeCreated;
use App\Modules\Messages\Events\TypeUpdating;
use App\Modules\Messages\Events\TypeUpdated;
use App\Modules\Messages\Events\TypeDeleted;

/**
 * Model for message type
 *
 * @property int    $id
 * @property int    $name
 * @property int    $resourceid
 * @property string $classname
 */
class Type extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'messagequeuetypes';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
	 */
	protected $attributes = [
		'resourceid' => 0
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'creating' => TypeCreating::class,
		'created'  => TypeCreated::class,
		'updating' => TypeUpdating::class,
		'updated'  => TypeUpdated::class,
		'deleted'  => TypeDeleted::class,
	];

	/**
	 * Set resource ID
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setResourceidAttribute($value): void
	{
		$this->attributes['resourceid'] = $this->stringToInteger($value);
	}

	/**
	 * Convert [!] Legacy string IDs to integers
	 *
	 * @param   mixed  $value
	 * @return  int
	 */
	private function stringToInteger($value): int
	{
		if (is_string($value))
		{
			$value = preg_replace('/[a-zA-Z\/]+\/(\d+)/', "$1", $value);
		}

		return (int)$value;
	}

	/**
	 * Defines a relationship to messages
	 *
	 * @return  HasMany
	 */
	public function messages(): HasMany
	{
		return $this->hasMany(Message::class, 'messagequeuetypeid');
	}

	/**
	 * Defines a relationship to resource
	 *
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}
}
