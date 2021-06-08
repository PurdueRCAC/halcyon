<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Groups\Models\Group;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * Storage model for a resource directory
 */
class Loan extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimestart';

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
	const DELETED_AT = 'datetimestop';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storagedirloans';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimestart',
		'datetimestop'
	);

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(StorageResource::class, 'resourceid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to a group
	 *
	 * @return  object
	 */
	public function lender()
	{
		return $this->belongsTo(Group::class, 'lendergroupid');
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @return  object
	 */
	public function hasStarted()
	{
		return ($this->datetimestart && $this->datetimestart != '0000-00-00 00:00:00' && $this->datetimestart != '-0001-11-30 00:00:00');
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @return  object
	 */
	public function hasEnd()
	{
		return ($this->datetimestop && $this->datetimestop != '0000-00-00 00:00:00' && $this->datetimestop != '-0001-11-30 00:00:00');
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @return  object
	 */
	public function hasEnded()
	{
		return ($this->hasEnd() && $this->datetimestop->toDateTimeString() <= Carbon::now()->toDateTimeString());
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhenAvailable($query)
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->where(function($where) use ($now)
			{
				$where->where('datetimestart', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestart', '<', $now);
			})
			->where(function($where) use ($now)
			{
				$where->where('datetimestop', '=', '0000-00-00 00:00:00')
					->orWhere('datetimestop', '>', $now);
			});
	}

	/**
	 * Set a query's WHERE clause to include published state
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhenNotAvailable($query)
	{
		$now = Carbon::now()->toDateTimeString();

		return $query->whereNotNull('datetimestop')
			->where('datetimestop', '!=', '0000-00-00 00:00:00')
			->where('datetimestop', '<=', $now);
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setBytesAttribute($value)
	{
		$value = str_replace(',', '', $value);
		
		$neg = false;

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*([PpTtGgMmKkBb]{1,2})$/", $value, $matches))
		{
			if ($matches[1][0] < 0)
			{
				$neg = true;
			}
			$num  = abs((int)$matches[1][0]);
			$unit = $matches[2][0];

			$value = $this->convertToBytes($num, $unit);
		}
		else
		{
			
			$value = intval($value);
		}

		$this->attributes['bytes'] = $neg ? -(int)$value : (int)$value;
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

	/**
	 * Get a list of usage
	 *
	 * @return  object
	 */
	public function getCounterAttribute()
	{
		return self::query()
			->where('datetimestart', '=', $this->datetimestart)
			->where('datetimestop', '=', ($this->hasEnd() ? $this->datetimestop : '0000-00-00 00:00:00'))
			->where('groupid', '=', $this->lendergroupid)
			->where('lendergroupid', '=', $this->groupid)
			->get()
			->first();
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  string
	 */
	public function getTypeAttribute()
	{
		return 'loan';
	}
}
