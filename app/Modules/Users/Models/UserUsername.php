<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Core\Traits\LegacyTrash;
//use App\Modules\Users\Events\UserUsernameCreating;
//use App\Modules\Users\Events\UserUsernameCreated;
//use App\Modules\Users\Events\UserUsernameDeleted;

/**
 * User Usernames
 */
class UserUsername extends Model
{
	use SoftDeletes, LegacyTrash;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
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
	protected $table = 'userusernames';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'username',
		'unixid',
		'userid'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datecreated',
		'dateremoved',
		'datelastseen'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'username' => 'required|string|min:1,max:16',
		'userid' => 'required|integer'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	/*protected $dispatchesEvents = [
		'creating' => UserUsernameCreating::class,
		'created'  => UserUsernameCreated::class,
		'deleted'  => UserUsernameDeleted::class,
	];*/

	/**
	 * Get notes
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * If user if the created timestamp is set
	 *
	 * @return  bool
	 **/
	public function isCreated()
	{
		return ($this->datecreated
			&& $this->datecreated != '0000-00-00 00:00:00'
			&& $this->datecreated != '-0001-11-30 00:00:00');
	}

	/**
	 * If user has logged in before
	 *
	 * @return  bool
	 **/
	public function hasVisited()
	{
		return ($this->datelastseen
			&& $this->datelastseen != '0000-00-00 00:00:00'
			&& $this->datelastseen != '-0001-11-30 00:00:00');
	}
}
