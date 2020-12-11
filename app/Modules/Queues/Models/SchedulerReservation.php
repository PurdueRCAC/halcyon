<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * Model for a scheduler reservation
 */
class SchedulerReservation extends Model
{
	use ErrorBag, Validatable, Historable;

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
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required|string|min:1|max:32',
		'nodes' => 'required|string|min:1|max:255',
		'schedulerid' => 'required|integer|min:1',
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Defines a relationship to scheduler
	 *
	 * @return  object
	 */
	public function scheduler()
	{
		return $this->belongsTo(Scheduler::class, 'schedulerid');
	}
}
