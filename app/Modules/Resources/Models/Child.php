<?php

namespace App\Modules\Resources\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model for a subresource mapping
 *
 * @property int    $id
 * @property int    $resourceid
 * @property int    $subresourceid
 */
class Child extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
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
	 * @var array<string,string>
	 */
	protected $rules = array(
		'resourceid' => 'required|integer|min:1',
		'subresourceid' => 'required|integer|min:1'
	);

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to a subresource
	 *
	 * @return  BelongsTo
	 */
	public function subresource(): BelongsTo
	{
		return $this->belongsTo(Subresource::class, 'subresourceid')->withTrashed();
	}
}
