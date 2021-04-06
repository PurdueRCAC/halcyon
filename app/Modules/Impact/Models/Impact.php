<?php

namespace App\Modules\Impact\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Model for an impact entry
 */
class Impact extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'impacts';

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
	public static $orderBy = 'sequence';

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
		'name' => 'required|string|max:255',
		'value' => 'required|string|max:255',
		'impacttableid' => 'required|integer',
	);

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'updateddatetime',
	];

	/**
	 * Defines a relationship to an impact table
	 *
	 * @return  object
	 */
	public function table()
	{
		return $this->belongsTo(Table::class, 'impacttableid');
	}
}
