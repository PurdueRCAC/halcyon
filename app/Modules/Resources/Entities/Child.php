<?php

namespace App\Modules\Resources\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for a subresource mapping
 */
class Child extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'resourcesubresources';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'resourceid' => 'required|integer|min:1',
		'subresourceid' => 'required|integer|min:1'
	);

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to a subresource
	 *
	 * @return  object
	 */
	public function subresource()
	{
		return $this->belongsTo(Subresource::class, 'subresourceid')->withTrashed();
	}
}
