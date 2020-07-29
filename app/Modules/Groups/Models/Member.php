<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Events\MemberCreating;
use App\Modules\Groups\Events\MemberCreated;
use App\Modules\Groups\Events\MemberUpdating;
use App\Modules\Groups\Events\MemberUpdated;
use App\Modules\Groups\Events\MemberDeleted;

/**
 * Group member model
 */
class Member extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'dateremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupusers';

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
		'groupid' => 'required|integer',
		'userid' => 'required|integer|min:1'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => MemberCreating::class,
		'created'  => MemberCreated::class,
		'updating' => MemberUpdating::class,
		'updated'  => MemberUpdated::class,
		'deleted'  => MemberDeleted::class,
	];

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
	 * Get parent user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid')->withTrashed();
			/*->withDefault([
				'id' => 0,
				'name' => trans('global.unknown')
			]);*/
	}

	/**
	 * Get user request
	 *
	 * @return  object
	 */
	public function request()
	{
		return $this->belongsTo('App\Modules\Groups\Models\UserRequest', 'userrequestid');
	}

	/**
	 * Get member type
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'membertype');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  bool
	 */
	public function isMember()
	{
		return $this->membertype == 1;
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  bool
	 */
	public function isManager()
	{
		return $this->membertype == 2;
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  bool
	 */
	public function isViewer()
	{
		return $this->membertype == 3;
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function scopeWhereIsMember($query)
	{
		return $query->where('membertype', '=', 1);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function scopeWhereIsManager($query)
	{
		return $query->where('membertype', '=', 2);
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function scopeWhereIsViewer($query)
	{
		return $query->where('membertype', '=', 3);
	}

	/**
	 * Get a record by group/user
	 *
	 * @param   integer  $groupid
	 * @param   integer  $userid
	 * @return  object
	 */
	public static function findByGroupAndUser($groupid, $userid)
	{
		return self::query()
			->where('groupid', '=', $groupid)
			->where('userid', '=', $userid)
			->first();
	}
}
