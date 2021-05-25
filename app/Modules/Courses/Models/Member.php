<?php

namespace App\Modules\Courses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Core\Traits\LegacyTrash;
use App\Modules\Courses\Events\MemberCreating;
use App\Modules\Courses\Events\MemberCreated;
use App\Modules\Courses\Events\MemberUpdating;
use App\Modules\Courses\Events\MemberUpdated;
use App\Modules\Courses\Events\MemberDeleted;
use Carbon\Carbon;

/**
 * Course member
 */
class Member extends Model
{
	use SoftDeletes, LegacyTrash, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

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
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'classusers';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimestart';

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
		'classaccountid' => 'required|integer|min:1',
		'userid' => 'required|integer|min:1',
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	public $dates = array(
		'datetimestart',
		'datetimestop',
		'datetimecreated',
		'datetimeremoved',
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
	 * If entry has started
	 *
	 * @return  bool
	 **/
	public function hasStarted()
	{
		return ($this->datetimestart && $this->datetimestart != '0000-00-00 00:00:00' && $this->datetimestart != '-0001-11-30 00:00:00');
	}

	/**
	 * If entry has stopped
	 *
	 * @return  bool
	 **/
	public function hasStopped()
	{
		return ($this->datetimestop && $this->datetimestop != '0000-00-00 00:00:00' && $this->datetimestop != '-0001-11-30 00:00:00');
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
	}

	/**
	 * Get the modifier of this entry
	 *
	 * @return  object
	 */
	public function account()
	{
		return $this->belongsTo(Account::class, 'classaccountid');
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
	 * Delete entry and associated data
	 *
	 * @return  bool
	 */
	public function delete(array $options = [])
	{
		$query = $this->setKeysForSaveQuery($this->newModelQuery());
		$query->update(['notice' => 2]);

		return parent::delete($options);
	}

	/**
	 * Delete entry and associated data
	 *
	 * @param   integer  $classaccountid
	 * @param   integer  $userid
	 * @return  bool
	 */
	public static function findByAccountAndUser($classaccountid, $userid)
	{
		return self::query()
			->withTrashed()
			->where('classaccountid', '=', $classaccountid)
			->where('userid', '=', $userid)
			->where(function($where)
			{
				$where->whereNull('datetimeremoved')
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->first();
	}
}
