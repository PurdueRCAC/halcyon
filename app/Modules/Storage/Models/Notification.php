<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\Timeperiod;
use App\Modules\Storage\Models\Notification\Type;

/**
 * Storage model for a notification
 */
class Notification extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirquotanotifications';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved',
		'datetimelastnotify'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved',
	];

	/**
	 * Set value in bytes
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function setValueAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\D+)$/", $value, $matches))
		{
			$num  = abs((int)$matches[1][0]);
			$unit = $matches[2][0];

			$value = $this->convertToBytes($num, $unit);
		}

		$this->attributes['value'] = $value;
	}

	/**
	 * Convert a value to bytes
	 *
	 * @param   integer  $num
	 * @param   string   $unit
	 * @return  integer
	 */
	private function convertToBytes($num, $unit)
	{
		$units = array(
			array("b", "bytes?"),
			array("ki?b?", "kilobytes?", "kibibytes?", "kbytes?"),
			array("mi?b?", "megabytes?", "mebibytes?", "mbytes?"),
			array("gi?b?", "gigabytes?", "gibibytes?", "gbytes?"),
			array("ti?b?", "terabytes?", "tebibytes?", "tbytes?"),
			array("pi?b?", "petabytes?", "pebibytes?", "pbytes?"),
			array("xi?b?", "exabytes?", "exibytes?", "xbytes?"),
		);

		$power = 0;
		foreach ($units as $unit_group)
		{
			foreach ($unit_group as $unit_regex)
			{
				if (preg_match("/^" . $unit_regex . "$/i", $unit))
				{
					break 2;
				}
			}
			$power++;
		}

		$mult = $num;
		for ($i=0; $i<$power; $i++)
		{
			$mult = $num*1024;
		}

		return $mult;
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withDefault();
	}

	/**
	 * Defines a relationship to timeperiod
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'timeperiodid')->withDefault();
	}

	/**
	 * Defines a relationship to directory
	 *
	 * @return  object
	 */
	public function directory()
	{
		return $this->belongsTo(Directory::class, 'storagedirid');
	}

	/**
	 * Defines a relationship to notification type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'storagedirquotanotificationtypeid');
	}
}
