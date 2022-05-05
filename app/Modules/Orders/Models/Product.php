<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Helpers\Currency;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Asset;
use Carbon\Carbon;

/**
 * Model for order product
 */
class Product extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
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
	protected $table = 'orderproducts';

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
		'id'
	];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'sequence';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Boot
	 *
	 * @return  void
	 */
	public static function boot()
	{
		parent::boot();

		self::creating(function($model)
		{
			// Set sequence value for new entries
			if (!$model->id)
			{
				$sequence = self::query()
					->where('ordercategoryid', '=', $model->ordercategoryid)
					->orderBy('sequence', 'desc')
					->first()
					->sequence;

				$model->sequence = intval($sequence) + 1;
			}
		});
	}

	/**
	 * Defines a relationship to a category
	 *
	 * @return  object
	 */
	public function category()
	{
		return $this->belongsTo(Category::class, 'ordercategoryid');
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->hasOne(Asset::class, 'id', 'resourceid');
	}

	/**
	 * Defines a relationship to a timeperiod
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->hasOne(Timeperiod::class, 'id', 'recurringtimeperiodid');
	}

	/**
	 * Get the access level
	 *
	 * @return  object
	 */
	public function viewlevel()
	{
		return $this->hasOne('App\Halcyon\Access\Viewlevel', 'id', 'public');
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getPriceAttribute()
	{
		return Currency::formatNumber($this->unitprice);
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getDecimalUnitpriceAttribute()
	{
		return str_replace(',', '', Currency::formatNumber($this->unitprice));
	}

	/**
	 * Set unit price
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setUnitpriceAttribute($value)
	{
		$this->attributes['unitprice'] = preg_replace('/[^0-9]+/', '', $value);
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   integer  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool     True on success.
	 */
	public function move($delta, $where = '')
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::query()
			->where('ordercategoryid', '=', $this->ordercategoryid);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('sequence', '<', (int) $this->sequence);
			$query->orderBy('sequence', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('sequence', '>', (int) $this->sequence);
			$query->orderBy('sequence', 'asc');
		}

		// Add the custom WHERE clause if set.
		if ($where)
		{
			$query->where(DB::raw($where));
		}

		// Select the first row with the criteria.
		$row = $query->first();

		// If a row is found, move the item.
		if ($row)
		{
			$prev = $this->sequence;

			// Update the ordering field for this instance to the row's ordering value.
			if (!$this->update(['sequence' => (int) $row->sequence]))
			{
				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			if (!$row->update(['sequence' => (int) $prev]))
			{
				return false;
			}
		}

		$all = self::query()
			->where('ordercategoryid', '=', $this->ordercategoryid)
			->orderBy('sequence', 'asc')
			->get();

		foreach ($all as $i => $row)
		{
			if ($row->sequence != ($i + 1))
			{
				$row->update(['sequence' => $i + 1]);
			}
		}

		return true;
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array  $pks    An array of primary key ids.
	 * @param   array  $order  An array of order values.
	 * @return  bool
	 */
	public static function saveorder(array $pks = [], array $order = [])
	{
		if (empty($pks))
		{
			return false;
		}

		// Update ordering values
		foreach ($pks as $i => $pk)
		{
			$model = self::findOrFail((int) $pk);

			if ($model->sequence != $order[$i])
			{
				$model->sequence = $order[$i];
				$model->save();
			}
		}

		return true;
	}
}
