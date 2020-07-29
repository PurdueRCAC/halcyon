<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Storage usage
 */
class Usage extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirusage';

	/**
	 * Uses timestamps
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimerecorded'
	);

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function directory()
	{
		return $this->belongsTo(Directory::class, 'storagedirid');
	}

	public function getTotalBlockUsageAttribute()
	{
		return ($this->space / 1024);
	}

	public function getBlockLimitAttribute()
	{
		return ($this->quota / 1024);
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function setQuotaAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $value, $matches))
		{
			$num  = abs((int)$matches[1][0]);
			$unit = $matches[2][0];

			$value = $this->convertToBytes($num, $unit);
		}
		else
		{
			$value = intval($value);
		}

		$this->attributes['quota'] = (int)$value;
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function setSpaceAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $value, $matches))
		{
			$num  = abs((int)$matches[1][0]);
			$unit = $matches[2][0];

			$value = $this->convertToBytes($num, $unit);
		}
		else
		{
			$value = intval($value);
		}

		$this->attributes['space'] = (int)$value;
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
}
