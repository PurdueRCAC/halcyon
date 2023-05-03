<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Model for a queue/user association
 *
 * @property int    $id
 * @property int    $queueid
 * @property Carbon|null $datetimestart
 * @property Carbon|null $datetimestop
 * @property int    $walltime
 */
class Walltime extends Model
{
	use Historable;

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
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasStart(): bool
	{
		return !is_null($this->datetimestart);
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnd(): bool
	{
		return !is_null($this->datetimestop);
	}

	/**
	 * Determine if in a trashed state
	 *
	 * @return  bool
	 */
	public function hasEnded(): bool
	{
		return ($this->hasEnd() && $this->datetimestop->timestamp < Carbon::now()->timestamp);
	}

	/**
	 * Defines a relationship to queue
	 *
	 * @return  BelongsTo
	 */
	public function queue(): BelongsTo
	{
		return $this->belongsTo(Queue::class, 'queueid');
	}

	/**
	 * Get walltime in human readable format
	 *
	 * @return  string
	 */
	public function getHumanWalltimeAttribute(): string
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
