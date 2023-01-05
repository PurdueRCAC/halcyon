<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Asset;

/**
 * Group node model
 */
class Node extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupnodes';

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
		'groupid' => 'required|integer|min:1',
		'resourceid' => 'required|integer|min:1'
	);

	/**
	 * Get parent group
	 *
	 * @return  object
	 */
	public function group()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Get associated resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid');
	}
}
