<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use App\Halcyon\Models\FieldOfScience as Field;

/**
 * Group Field of Science map
 */
class FieldOfScience extends Model
{
	use Historable;

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
	protected $table = 'groupfieldofscience';

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
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'groupid' => 'required|integer',
		'fieldofscienceid' => 'required|integer'
	);

	/**
	 * Field of science
	 *
	 * @return  object
	 */
	public function field()
	{
		return $this->belongsTo(Field::class, 'fieldofscienceid');
	}

	/**
	 * Group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}
}
