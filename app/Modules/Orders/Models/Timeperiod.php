<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Order model for timeperiod
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
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'timeperiods';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Defines a relationship to a warning timeperiod
	 *
	 * @return  HasOne
	 */
	public function warningtimeperiod(): HasOne
	{
		return $this->hasOne(self::class, 'id', 'warningtimeperiodid');
	}
}
