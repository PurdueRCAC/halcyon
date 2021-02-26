<?php

namespace App\Modules\Storage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Core\Traits\LegacyTrash;
use App\Modules\History\Traits\Historable;

/**
 * Storage resource model
 */
class StorageResource extends Model
{
	use Historable, SoftDeletes, LegacyTrash;

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
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'storageresources';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated',
		'datetimeremoved'
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
	 * Defines a relationship to directories
	 *
	 * @return  object
	 */
	public function directories()
	{
		return $this->hasMany(Directory::class, 'storageresourceid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo('App\Modules\Resources\Entities\Asset', 'parentresourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function quotaType()
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'getquotatypeid');
	}

	/**
	 * Defines a relationship to loans
	 *
	 * @return  object
	 */
	public function createType()
	{
		return $this->belongsTo('App\Modules\Messages\Models\Type', 'createtypeid');
	}

	/**
	 * Find a record by name
	 *
	 * @return  object
	 */
	public static function findByName($name)
	{
		return self::query()
			->where('name', '=', $name)
			->orWhere('name', 'like', $name . '%')
			->orWhere('name', 'like', '%' . $name)
			->orderBy('name', 'asc')
			->limit(1)
			->get()
			->first();
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function setDefaultquotaspaceAttribute($value)
	{
		$value = str_replace(',', '', $value);

		if (preg_match_all("/^(\-?\d*\.?\d+)\s*(\w+)$/", $value, $matches))
		{
			$num  = abs((int)$matches[1][0]);
			$unit = strtolower($matches[2][0]);

			$value = $this->convertToBytes($num, $unit);
		}
		else
		{
			$value = intval($value);
		}

		$this->attributes['defaultquotaspace'] = (int)$value;
	}

	/**
	 * Set value in bytes
	 *
	 * @param   mixed
	 * @return  void
	 */
	public function setDefaultquotafileAttribute($value)
	{
		$value = str_replace(',', '', $value);

		$this->attributes['defaultquotafile'] = (int)$value;
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
