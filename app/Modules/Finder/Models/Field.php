<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;

/**
 * Finder field
 *
 * @property int    $id
 * @property string $name
 * @property string $label
 * @property int    $weight
 * @property int    $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Field extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'finder_fields';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'label';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|max:255',
		'label' => 'required|string|max:255',
		'status' => 'nullable|integer'
	);

	/**
	 * Field Services
	 *
	 * @return  HasMany
	 */
	public function services(): HasMany
	{
		return $this->hasMany(ServiceField::class, 'field_id');
	}

	/**
	 * Retrieves one row loaded by name
	 *
	 * @param   string   $name
	 * @return  Field|null
	 */
	public static function findByName($name)
	{
		return self::query()
			->where('name', '=', (string)$name)
			->limit(1)
			->first();
	}

	/**
	 * Generates automatic name field value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setNameAttribute($value): void
	{
		$alias = strip_tags($value);
		$alias = trim($alias);
		if (strlen($alias) > 150)
		{
			$alias = substr($alias . ' ', 0, 150);
			$alias = substr($alias, 0, strrpos($alias, ' '));
		}
		$alias = str_replace(' ', '_', $alias);

		$this->attributes['name'] = preg_replace("/[^a-zA-Z0-9\_]/", '', strtolower($alias));
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(weight) + 1 AS seq'))
				->get()
				->first()
				->seq;

			$model->setAttribute('weight', (int)$result);
		});

		static::deleted(function ($model)
		{
			foreach ($model->services as $service)
			{
				$service->delete();
			}
		});
	}
}
