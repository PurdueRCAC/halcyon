<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Utility\Number;
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
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimerecorded' => 'datetime:Y-m-d H:i:s',
	];

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
	 * @return  int
	 */
	public function getTotalBlockUsageAttribute()
	{
		return ($this->space / 1024);
	}

	/**
	 * Get block limit
	 *
	 * @return  int
	 */
	public function getBlockLimitAttribute()
	{
		return ($this->quota / 1024);
	}

	/**
	 * Get normal variability
	 *
	 * @return  int
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
		$this->attributes['quota'] = Number::toBytes($value);
	}

	/**
	 * Get quota in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedQuotaAttribute()
	{
		return Number::formatBytes($this->quota);
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
	 * @return  int
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
		$this->attributes['space'] = Number::toBytes($value);
	}

	/**
	 * Get space in human readable format
	 *
	 * @return  string
	 */
	public function getFormattedSpaceAttribute()
	{
		return Number::formatBytes($this->space);
	}
}
