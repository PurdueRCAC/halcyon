<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Group member type model
 */
class Type extends Model
{
	use Historable;

	/**
	 * Membership type levels
	 **/
	const MEMBER  = 1;
	const MANAGER = 2;
	const VIEWER  = 3;
	const PENDING = 4;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'membertypes';

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
		'name' => 'required|string|max:20'
	);
}
