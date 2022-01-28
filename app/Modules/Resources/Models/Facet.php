<?php
namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Resource facet model
 *
 * This should probably be called `Attribute` but
 * the name causes conflicts with Laravel
 */
class Facet extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'resource_facets';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'key';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	public $rules = array(
		'resource_id' => 'required|integer|min:1',
		'facet_type_id' => 'required|string|max:255',
		'value' => 'required|string|max:512'
	);

	/**
	 * Get parent resource
	 *
	 * @return  object
	 */
	public function asset()
	{
		return $this->belongsTo(Asset::class, 'asset_id');
	}

	/**
	 * Get parent resource
	 *
	 * @return  object
	 */
	public function facetType()
	{
		return $this->belongsTo(FacetType::class, 'facet_type_id');
	}
}
