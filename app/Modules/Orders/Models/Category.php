<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;

/**
 * Model for news type
 */
class Category extends Model
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
	protected $table = 'ordercategories';

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
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function products()
	{
		return $this->hasMany(Product::class, 'ordercategoryid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(self::class, 'parentordercategoryid');
	}

	/**
	 * Defines a relationship to child categories
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->hasMany(self::class, 'parentordercategoryid');
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
			 || $this->{$this->getDeletedAtColumn()} == '-0001-11-30 00:00:00')
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

	/**
	 * Delete entry and associated data
	 *
	 * @param   array  $options
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		foreach ($this->products as $row)
		{
			$row->delete();
		}

		foreach ($this->children as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}
}
