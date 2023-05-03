<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\Queues\Events\TypeCreating;
use App\Modules\Queues\Events\TypeCreated;
use App\Modules\Queues\Events\TypeUpdating;
use App\Modules\Queues\Events\TypeUpdated;
use App\Modules\Queues\Events\TypeDeleted;
use App\Modules\History\Traits\Historable;

/**
 * Model for a queue type
 *
 * @property int    $id
 * @property string $name
 */
class Type extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queuetypes';

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

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
	 * Defines a relationship to queues
	 *
	 * @return  HasMany
	 */
	public function queues(): HasMany
	{
		return $this->hasMany(Queue::class, 'queuetype');
	}
}
