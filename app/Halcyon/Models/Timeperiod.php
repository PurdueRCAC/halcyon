<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Timeperiod
 */
class Timeperiod extends Model
{
	use ErrorBag, Validatable, Historable;

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
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * Field of science
	 *
	 * @return  object
	 */
	public function warningTime()
	{
		return $this->belongsTo(self::class, 'warningtimeperiodid');
	}

	/**
	 * Calculate date from another date
	 *
	 * @param   string  $dt
	 * @return  string
	 */
	public function calculateDateFrom($dt)
	{
		$dt = Carbon::parse($dt);

		return $dt
			->modify('- ' + $this->months)
			->modify('- ' + $this->unixtime)
			->toDateTimeString();
	}
}
