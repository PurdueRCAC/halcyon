<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Finder Service/Facet mapping
 *
 * @property int    $id
 * @property int    $service_id
 * @property int    $facet_id
 */
class ServiceFacet extends Model
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
	protected $table = 'finder_service_facets';

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
	public function facet(): BelongsTo
	{
		return $this->belongsTo(Facet::class, 'facet_id');
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
	 * Retrieves one row loaded by service_id and facet_id
	 *
	 * @param   int  $service_id
	 * @param   int  $facet_id
	 * @return  ServiceFacet|null
	 */
	public static function findByServiceAndFacet($service_id, $facet_id)
	{
		return self::query()
			->where('service_id', '=', (integer)$service_id)
			->where('facet_id', '=', (integer)$facet_id)
			->limit(1)
			->first();
	}
}
