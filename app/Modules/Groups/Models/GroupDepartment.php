<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Group department association
 */
class GroupDepartment extends Model
{
	use Historable;

	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupcollegedept';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'percentage';

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
		'groupid' => 'required',
		'collegedeptid' => 'required'
	);

	/**
	 * Department
	 *
	 * @return  object
	 */
	public function department()
	{
		return $this->belongsTo(Department::class, 'collegedeptid');
	}

	/**
	 * Department
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}
}
