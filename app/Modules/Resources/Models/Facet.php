<?php
namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Resource facet model
 *
 * This should probably be called `Attribute` but
 * the name causes conflicts with Laravel
 *
 * @property int    $id
 * @property int    $facet_type_id
 * @property int    $asset_id
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
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
	 * @var  array<string,string>
	 */
	public $rules = array(
		'resource_id' => 'required|integer|min:1',
		'facet_type_id' => 'required|string|max:255',
		'value' => 'required|string|max:512'
	);

	/**
	 * Get parent resource
	 *
	 * @return  BelongsTo
	 */
	public function asset(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'asset_id');
	}

	/**
	 * Get parent resource
	 *
	 * @return  BelongsTo
	 */
	public function facetType(): BelongsTo
	{
		return $this->belongsTo(FacetType::class, 'facet_type_id');
	}
}
