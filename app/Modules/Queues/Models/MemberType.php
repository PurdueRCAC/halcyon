<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Model for a queue membertype
 *
 * @property int    $id
 * @property string $name
 */
class MemberType extends Model
{
	use Historable;

	/**
	 * Membership status
	 *
	 * @var int
	 */
	const MEMBER  = 1;
	const MANAGER = 2;
	const VIEWER  = 3;
	const PENDING = 4;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'membertypes';

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
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|min:1|max:20'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];
}
