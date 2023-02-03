<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Model for a scheduler reservation
 *
 * @property int    $id
 * @property int    $schedulerid
 * @property string $name
 * @property string $nodes
 * @property Carbon|null $datetimestart
 * @property Carbon|null $datetimestop
 */
class SchedulerReservation extends Model
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
	protected $table = 'schedulerreservations';

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
		'name' => 'required|string|min:1|max:32',
		'nodes' => 'required|string|min:1|max:255',
		'schedulerid' => 'required|integer|min:1',
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
	 * Defines a relationship to scheduler
	 *
	 * @return  BelongsTo
	 */
	public function scheduler(): BelongsTo
	{
		return $this->belongsTo(Scheduler::class, 'schedulerid');
	}
}
