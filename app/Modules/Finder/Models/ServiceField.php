<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Service/Field mapping
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
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'service_id' => 'required|integer',
		'field_id' => 'required|integer',
		'value' => 'required|string|max:1200'
	);

	/**
	 * Field
	 *
	 * @return  object
	 */
	public function field()
	{
		return $this->belongsTo(Field::class, 'field_id');
	}

	/**
	 * Service
	 *
	 * @return  object
	 */
	public function service()
	{
		return $this->belongsTo(Service::class, 'service_id');
	}

	/**
	 * Retrieves one row loaded by service_id and field_id
	 *
	 * @param   integer  $service_id
	 * @param   integer  $field_id
	 * @return  object
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
