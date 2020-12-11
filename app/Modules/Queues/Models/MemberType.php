<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;
/*use App\Modules\Queues\Events\MemberTypeCreating;
use App\Modules\Queues\Events\MemberTypeCreated;
use App\Modules\Queues\Events\MemberTypeUpdating;
use App\Modules\Queues\Events\MemberTypeUpdated;
use App\Modules\Queues\Events\MemberTypeDeleted;*/

/**
 * Model for a queue membertype
 */
class MemberType extends Model
{
	use Historable;

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
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required|string|min:1|max:20'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	/*protected $dispatchesEvents = [
		'creating' => MemberTypeCreating::class,
		'created'  => MemberTypeCreated::class,
		'updating' => MemberTypeUpdating::class,
		'updated'  => MemberTypeUpdated::class,
		'deleted'  => MemberTypeDeleted::class,
	];*/
}
