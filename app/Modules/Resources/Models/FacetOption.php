<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for a resource facet type option
 *
 * @property int    $id
 * @property int    $facet_type_id
 * @property string $value
 * @property string $label
 * @property int    $ordering
 * @property int    $checked
 */
class FacetOption extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'resource_facet_type_options';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

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
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|max:255'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'ordering';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Defines a relationship to facet type
	 *
	 * @return  BelongsTo
	 */
	public function facetType(): BelongsTo
	{
		return $this->belongsTo(FacetType::class, 'facet_type_id');
	}
}
