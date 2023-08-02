<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Modules\History\Traits\Historable;

/**
 * Order category
 *
 * @property int    $id
 * @property int    $parentordercategoryid
 * @property string $name
 * @property string $description
 * @property Carbon|string|null $datetimecreated
 * @property Carbon|string|null $datetimeremoved
 * @property int    $sequence
 */
class Category extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'ordercategories';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
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
	public static function boot(): void
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

		self::deleted(function($model)
		{
			// Clean up related objects
			foreach ($model->products as $row)
			{
				$row->delete();
			}

			foreach ($model->children as $row)
			{
				$row->delete();
			}
		});
	}

	/**
	 * Defines a relationship to products
	 *
	 * @return  HasMany
	 */
	public function products(): HasMany
	{
		return $this->hasMany(Product::class, 'ordercategoryid');
	}

	/**
	 * Defines a relationship to parent category
	 *
	 * @return  BelongsTo
	 */
	public function parent(): BelongsTo
	{
		return $this->belongsTo(self::class, 'parentordercategoryid');
	}

	/**
	 * Defines a relationship to child categories
	 *
	 * @return  HasMany
	 */
	public function children(): HasMany
	{
		return $this->hasMany(self::class, 'parentordercategoryid');
	}

	/**
	 * Get an alias (URL slug)
	 *
	 * @return  string
	 */
	public function getAliasAttribute(): string
	{
		$alias = strtolower($this->name);
		$alias = str_replace(' ', '-', $alias);
		$alias = preg_replace('/[^a-z0-9\-_]+/', '', $alias);
		return $alias;
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   int  $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string   $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool     True on success.
	 */
	public function move($delta, $where = ''): bool
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::query()
			->where('parentordercategoryid', '=', $this->parentordercategoryid);

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
			->where('parentordercategoryid', '=', $this->parentordercategoryid)
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
	public static function saveorder(array $pks = [], array $order = []): bool
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
