<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
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
		return $this->hasOne('\\App\\Modules\\Resources\\Entities\\Asset', 'resourceid');
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
		$number = preg_replace('/[^0-9\-]/', '', $this->unitprice);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return '$' . $neg . $number;
	}

	/**
	 * Set unit price
	 *
	 * @return  void
	 */
	public function setUnitpriceAttribute($value)
	{
		$this->attributes['unitprice'] = preg_replace('/[^0-9]+/', '', $value);
	}

	/**
	 * Determine if the model instance has been soft-deleted.
	 *
	 * @return bool
	 */
	public function isTrashed()
	{
		$result = $this->trashed();

		if ($result)
		{
			if ($this->{$this->getDeletedAtColumn()} == '0000-00-00 00:00:00'
			 || $this->{$this->getDeletedAtColumn()} == '-0001-11-30 00:00:00'
			 || $this->{$this->getDeletedAtColumn()} >= Carbon::now()->toDateTimeString())
			{
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();
		$c = $this->getDeletedAtColumn();

		return $query->where(function($where) use ($t, $c)
		{
			$where->whereNull($t . '.' . $c)
					->orWhere($t . '.' . $c, '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();
		$c = $this->getDeletedAtColumn();

		return $query->where(function($where) use ($t, $c)
		{
			$where->whereNotNull($t . '.' . $c)
				->where($t . '.' . $c, '!=', '0000-00-00 00:00:00');
		});
	}
}
