<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Courses\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Entities\Asset;
use App\Modules\Courses\Events\AccountCreating;
use App\Modules\Courses\Events\AccountCreated;
use App\Modules\Courses\Events\AccountUpdating;
use App\Modules\Courses\Events\AccountUpdated;
use App\Modules\Courses\Events\AccountDeleted;
use Carbon\Carbon;

/**
 * Cron model for a job
 */
class Account extends Model
{
	use SoftDeletes, Historable;

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
	protected $table = 'classaccounts';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'crn';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'resourceid' => 'integer',
		'datetimestart' => 'datetime',
		'datetimestop' => 'datetime',
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'crn' => 'required|string|max:8',
		'department' => 'required|string|max:4',
		'coursenumber' => 'required|string|max:8',
		'classname' => 'required|string|max:255',
		'resourceid' => 'required|integer|min:1',
		'groupid' => 'required|integer|min:1',
		'userid' => 'required|integer|min:1',
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => AccountCreating::class,
		'created'  => AccountCreated::class,
		'updating' => AccountUpdating::class,
		'updated'  => AccountUpdated::class,
		'deleted'  => AccountDeleted::class,
	];

	/**
	 * A report
	 *
	 * @var string
	 */
	public $report = null;

	/**
	 * Get a list of users
	 *
	 * @return  object
	 */
	public function members()
	{
		return $this->hasMany(Member::class, 'classaccountid');
	}

	/**
	 * Owner
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'userid');
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

		foreach ($this->members as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}
}
