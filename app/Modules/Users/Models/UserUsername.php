<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * User Usernames
 *
 * @property int    $id
 * @property int    $userid
 * @property string $username
 * @property string $email
 * @property int    $unixid
 * @property Carbon|null $datecreated
 * @property Carbon|null $dateremoved
 * @property Carbon|null $datelastseen
 * @property Carbon|null $dateverified
 */
class UserUsername extends Model
{
	use SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'dateremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'userusernames';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $fillable = [
		'username',
		'email',
		'unixid',
		'userid'
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datelastseen' => 'datetime:Y-m-d H:i:s',
		'dateverified' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * Set username value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setUsernameAttribute(string $value): void
	{
		$value = strip_tags($value);
		$value = strtolower($value);
		$value = preg_replace('/[^a-z0-9-]/', '', $value);

		$this->attributes['username'] = $value;
	}

	/**
	 * Set email value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setEmailAttribute($value): void
	{
		if (!filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			$value = null;
		}

		$this->attributes['email'] = $value;
	}

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
	public function isCreated(): bool
	{
		return !is_null($this->datecreated);
	}

	/**
	 * If user has logged in before
	 *
	 * @return  bool
	 **/
	public function hasVisited(): bool
	{
		return !is_null($this->datelastseen);
	}

	/**
	 * If user if the verified timestamp is set
	 *
	 * @return  bool
	 **/
	public function isEmailVerified(): bool
	{
		return !is_null($this->dateverified);
	}
}
