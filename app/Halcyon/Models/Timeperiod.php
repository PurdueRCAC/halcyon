<?php

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Timeperiod
 *
 * @property int    $id
 * @property string $name
 * @property string $singular
 * @property string $plural
 * @property int    $unixtime
 * @property int    $months
 * @property int    $warningtimeperiodid
 */
class Timeperiod extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'timeperiods';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

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
	 * Get the warning time period
	 *
	 * @return  BelongsTo
	 */
	public function warningTime(): BelongsTo
	{
		return $this->belongsTo(self::class, 'warningtimeperiodid');
	}

	/**
	 * Calculate date from another date
	 *
	 * @param   string  $dt
	 * @return  string
	 */
	public function calculateDateFrom($dt): string
	{
		return Carbon::parse($dt)
			->modify('- ' + $this->months)
			->modify('- ' + $this->unixtime)
			->toDateTimeString();
	}
}
