<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

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
	 * Defines a relationship to a directory
	 *
	 * @return  object
	 */
	public function directory()
	{
		return $this->belongsTo(Directory::class, 'storagedirid');
	}

	/**
	 * Get total block usage
	 *
	 * @return  integer
	 */
	public function getTotalBlockUsageAttribute()
	{
		return ($this->space / 1024);
	}

	/**
	 * Get block limit
	 *
	 * @return  integer
	 */
	public function getBlockLimitAttribute()
	{
		return ($this->quota / 1024);
	}

	/**
	 * Get normal variability
	 *
	 * @return  integer
	 */
	public function getNormalvariabilityAttribute()
	{
		/*
		SELECT resourceid, 
				storagedirid, 
				quota AS lastquota, 
				space AS lastspace, 
				lastcheck, 
				lastinterval,
				LEAST(1, (SUM(tb1.var) / SUM(tb1.max)) * GREATEST(1, 5 * POW((space / quota) , 28))) AS normalvariability FROM 
					(SELECT storagedirusage.id, 
						storagedirs.resourceid, 
						storagedirusage.storagedirid, 
						storagedirusage.quota, 
						storagedirusage.space, 
						storagedirusage.lastinterval, 
						MAX(storagedirusage.datetimerecorded) AS lastcheck,
						LEFT(storagedirusage.datetimerecorded, 10) AS day,
						(((COUNT(DISTINCT storagedirusage.space)-1) / COUNT(storagedirusage.space)) * EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT(storagedirusage.datetimerecorded, 10)))/86400)+1)*0.25)) as var,
							(EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT(storagedirusage.datetimerecorded, 10)))/86400)+1)*0.25)) AS max 
					FROM storagedirusage, 
						storagedirs 
					WHERE storagedirusage.datetimerecorded >= DATE_SUB(NOW(), INTERVAL 10 DAY) AND 
						storagedirusage.storagedirid <> 0 
						AND (storagedirusage.quota <> 0 OR storagedirusage.space <> 0) 
						AND storagedirs.id = storagedirusage.storagedirid 
					GROUP BY storagedirusage.storagedirid, 
						day 
					ORDER BY storagedirusage.storagedirid, 
						storagedirusage.datetimerecorded DESC) AS tb1 
			GROUP BY tb1.storagedirid
		*/

		$d = $this->getTable();

		$row = self::query()
			->select(
				$d . '.*',
				DB::raw('(((COUNT(DISTINCT ' . $d . '.space)-1) / COUNT(' . $d . '.space)) * EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT(' . $d . '.datetimerecorded, 10)))/86400)+1)*0.25)) AS var'),
				DB::raw('(EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT(' . $d . '.datetimerecorded, 10)))/86400)+1)*0.25)) AS max')
			)
			->where('storagedirid', '=', $this->storagedirid)
			->where('datetimerecorded', '>=', Carbon::now()->modify('-10 days')->toDateTimeString())
			->where(function($where)
			{
				$where->where('quota', '<>', 0)
					->orWhere('space', '<>', 0);
			})
			->orderBy('datetimerecorded', 'desc')
			->groupBy('id')
			->groupBy('storagedirid')
			->limit(1)
			->get()
			->first();

		if (!$row)
		{
			return 0;
		}

		return min(1, ($row->var / $row->max) * max(1, 5 * pow(($row->space / $row->quota) , 28)));
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setQuotaAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*([PpTtGgMmKkiBb]{1,3})$/", $value, $matches))
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
	 * Set storagedirid
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setStoragediridAttribute($value)
	{
		$this->attributes['storagedirid'] = $this->stringToInteger($value);
	}

	/**
	 * Convert [!] Legacy string IDs to integers
	 *
	 * @param   mixed  $value
	 * @return  integer
	 */
	private function stringToInteger($value)
	{
		if (is_string($value))
		{
			$value = preg_replace('/[a-zA-Z\/]+\/(\d+)/', "$1", $value);
		}

		return (int)$value;
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setSpaceAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*([PpTtGgMmKkiBb]{1,3})$/", $value, $matches))
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
			$mult = $mult*1024;
		}

		return $mult;
	}
}
