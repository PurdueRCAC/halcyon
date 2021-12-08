<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
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
}
