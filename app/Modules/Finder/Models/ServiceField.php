<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Finder Service/Field mapping
 *
 * @property int    $id
 * @property int    $service_id
 * @property int    $field_id
 * @property string $value
 */
class ServiceField extends Model
{
	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'finder_service_fields';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

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
	 * Field
	 *
	 * @return  BelongsTo
	 */
	public function field(): BelongsTo
	{
		return $this->belongsTo(Field::class, 'field_id');
	}

	/**
	 * Service
	 *
	 * @return  BelongsTo
	 */
	public function service(): BelongsTo
	{
		return $this->belongsTo(Service::class, 'service_id');
	}

	/**
	 * Retrieves one row loaded by service_id and field_id
	 *
	 * @param   int  $service_id
	 * @param   int  $field_id
	 * @return  ServiceField|null
	 */
	public static function findByServiceAndField($service_id, $field_id)
	{
		return self::query()
			->where('service_id', '=', (integer)$service_id)
			->where('field_id', '=', (integer)$field_id)
			->limit(1)
			->first();
	}
}
