<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for a resource facet type
 */
class FacetType extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'resource_facet_types';

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
		'name' => 'required|string|max:20'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Defines a relationship to options
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'type_id');
	}

	/**
	 * Defines a relationship to options
	 *
	 * @return  object
	 */
	public function options()
	{
		return $this->hasMany(FacetOption::class, 'facet_type_id');
	}

	/**
	 * Defines a relationship to facets
	 *
	 * @return  object
	 */
	public function facets()
	{
		return $this->hasMany(Facet::class, 'facet_type_id');
	}

	/**
	 * Set name
	 *
	 * @param   mixed  $value
	 * @return  void
	 */
	public function setNameAttribute($value)
	{
		$value = strip_tags($value);
		$value = trim($value);
		$value = strtolower($value);
		//$value = str_replace(' ', '-', $value);
		$value = preg_replace("/[^a-zA-Z0-9_]/", '', $value);

		$this->attributes['name'] = $value;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array    $options
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->options as $row)
		{
			$row->delete();
		}

		foreach ($this->facets as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}
}
