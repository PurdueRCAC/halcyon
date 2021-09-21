<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Model for a queue/user association
 */
class Walltime extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * Timestamps
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'queuewalltimes';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'queueid' => 'required|integer|min:1'
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
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStart()
	{
		return ($this->datetimestart && $this->datetimestart != '0000-00-00 00:00:00' && $this->datetimestart != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnd()
	{
		return ($this->datetimestop && $this->datetimestop != '0000-00-00 00:00:00' && $this->datetimestop != '-0001-11-30 00:00:00');
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnded()
	{
		return ($this->hasEnd() && $this->datetimestop->timestamp < Carbon::now()->timestamp);
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function queue()
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  string
	 */
	public function getHumanWalltimeAttribute()
	{
		$walltime = $this->walltime;
		$unit = '';

		if ($walltime < 60)
		{
			$unit = 'sec';
		}
		else if ($walltime < 3600)
		{
			$walltime /= 60;
			$unit = 'min';
		}
		else if ($walltime < 86400)
		{
			$walltime /= 3600;
			$unit = 'hrs';
		}
		else
		{
			$walltime /= 86400;
			$unit = 'days';
		}

		return round($walltime) . ' ' . $unit;
	}
}
