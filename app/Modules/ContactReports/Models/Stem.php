<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ContactReports model for stemmed text
 */
class Stem extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * This will default to #__{namespace}_{modelName} unless otherwise
	 * overwritten by a given subclass. Definition of this property likely
	 * indicates some derivation from standard naming conventions.
	 *
	 * @var  string
	 **/
	protected $table = 'contactreportstems';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'stemmedtext' => 'required'
	);

	/**
	 * Defines a relationship to a report
	 *
	 * @return  object
	 */
	public function report()
	{
		return $this->belongsTo(__NAMESPACE__ . '\\Report', 'id');
	}
}
