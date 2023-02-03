<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\History\Traits\Historable;

/**
 * Model for a scheduler policy
 *
 * @property int    $id
 * @property string $code
 * @property string $name
 */
class SchedulerPolicy extends Model
{
	use Historable;

	/**
	 * Use timestamps
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'schedulerpolicies';

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
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|min:1|max:64'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  HasMany
	 */
	public function schedulers(): HasMany
	{
		return $this->hasMany(Scheduler::class, 'schedulerpolicyid');
	}
}
