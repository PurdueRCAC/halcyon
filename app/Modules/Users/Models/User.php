<?php
namespace App\Modules\Users\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Str;
use App\Halcyon\Access\Gate;
use App\Halcyon\Access\Map;
use App\Halcyon\Access\Role;
use App\Modules\Users\Events\UserCreating;
use App\Modules\Users\Events\UserCreated;
use App\Modules\Users\Events\UserUpdating;
use App\Modules\Users\Events\UserUpdated;
use App\Modules\Users\Events\UserDeleted;
use App\Modules\Users\Events\UserLookup;
use App\Modules\Users\Entities\LetterAvatar;
use Lab404\Impersonate\Models\Impersonate;
use Carbon\Carbon;

/**
 * User model
 */
class User extends Model implements
	AuthenticatableContract,
	AuthorizableContract
{
	use Authenticatable, Notifiable, Impersonate, CanResetPassword;

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'newroles',
		'api_token',
		'puid'
	];

	/**
	 * The accessors to append to the model's array form.
	 *
	 * @var array
	 */
	protected $appends = ['username', 'unixid', 'datecreated', 'dateremoved', 'email'];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'name'  => 'required|string|min:1,max:128',
		'api_token' => 'nullable|string|max:100'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'creating' => UserCreating::class,
		'created'  => UserCreated::class,
		'updating' => UserUpdating::class,
		'updated'  => UserUpdated::class,
		'deleted'  => UserDeleted::class,
	];

	/**
	 * The current UserUsername instance
	 *
	 * @var object
	 */
	protected $userusername = null;

	/**
	 * Determine if the entity has a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function can($ability, $arguments = [])
	{
		if (empty($arguments))
		{
			$arguments = null;
		}

		// Make sure we only check for core.admin once during the run.
		if ($this->isRoot === null)
		{
			$this->isRoot = false;

			// Check for the configuration file failsafe.
			$rootUser = config('root_user');

			// The root_user variable can be a numeric user ID or a username.
			if (is_numeric($rootUser) && $this->id > 0 && $this->id == $rootUser)
			{
				$this->isRoot = true;
			}
			elseif ($this->username && $this->username == $rootUser)
			{
				$this->isRoot = true;
			}
			else
			{
				// Get all roles against which the user is mapped.
				$identities = $this->getAuthorisedRoles();

				array_unshift($identities, $this->id * -1);

				if (Gate::getAssetRules(1)->allow('admin', $identities))
				{
					$this->isRoot = true;
				}
			}
		}

		return $this->isRoot ? true : Gate::check($this->id, $ability, $arguments);
	}

	/**
	 * If user is trashed
	 *
	 * @return  bool
	 **/
	public function trashed()
	{
		return $this->getUserUsername()->trashed();
	}

	/**
	 * If user created timestamp is set
	 *
	 * @return  bool
	 **/
	public function isCreated()
	{
		return !is_null($this->getUserUsername()->datecreated);
	}

	/**
	 * If user has an active session
	 *
	 * @return  bool
	 **/
	public function isOnline()
	{
		$lifetime = Carbon::now()->modify('- ' . config('session.lifetime', 120) . ' minutes')->timestamp;

		foreach ($this->sessions as $session)
		{
			if ($session->last_activity->timestamp > $lifetime)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the active UserUsername instance
	 *
	 * @return  object  UserUsername
	 **/
	public function getUserUsername()
	{
		if (is_null($this->userusername))
		{
			$this->userusername = $this->usernames()
				->withTrashed()
				->orderBy('dateremoved', 'asc')
				->orderBy('datecreated', 'desc')
				->first();

			if (!$this->userusername)
			{
				$this->userusername = new UserUsername;
				$this->userusername->userid = $this->id;
			}
		}

		return $this->userusername;
	}

	/**
	 * If user has logged in before
	 *
	 * @return  bool
	 **/
	public function hasVisited()
	{
		return !is_null($this->getUserUsername()->datelastseen);
	}

	/**
	 * Generate permissions for the modules provided
	 *
	 * @param  array $names
	 * @return array
	 */
	public function setModulePermissionsAttribute(array $names)
	{
		$value = [];

		$permissions = array(
			'edit',
			'edit.own',
			'edit.state',
			'create',
			'manage',
			'delete'
		);

		if (is_array($names) && count($names) > 0)
		{
			foreach ($names as $name)
			{
				foreach ($permissions as $permission)
				{
					$value[$name]['can'][$permission] = $this->can("$permission $name");
				}
			}
		}

		$this->attributes['module_permissions'] = $value;
	}

	/**
	 * Get permissions of the modules provided
	 *
	 * @return array
	 */
	public function getModulePermissionsAttribute()
	{
		if (isset($this->attributes['module_permissions']))
		{
			return $this->attributes['module_permissions'];
		}

		return array();
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  integer
	 */
	public function getUnixidAttribute()
	{
		return $this->getUserUsername()->unixid;
	}

	/**
	 * Gets date created
	 *
	 * @return  string
	 */
	public function getDatelastseenAttribute()
	{
		return $this->getLastVisitAttribute();
	}

	/**
	 * Gets date created
	 *
	 * @return  string
	 */
	public function getDatecreatedAttribute()
	{
		return $this->getCreatedAtAttribute();
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  string
	 */
	public function getDateremovedAttribute()
	{
		if (isset($this->attributes['dateremoved']))
		{
			if (!($this->attributes['dateremoved'] instanceof Carbon))
			{
				$this->attributes['dateremoved'] = Carbon::parse($this->attributes['dateremoved']);
			}
			return $this->attributes['dateremoved'];
		}
		return $this->getUserUsername()->dateremoved;
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  string
	 */
	public function getUsernameAttribute()
	{
		if (isset($this->attributes['username']))
		{
			return $this->attributes['username'];
		}
		return $this->getUserUsername()->username;
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  string
	 */
	public function getCreatedAtAttribute()
	{
		if (isset($this->attributes['datecreated']))
		{
			if (!($this->attributes['datecreated'] instanceof Carbon))
			{
				$this->attributes['datecreated'] = Carbon::parse($this->attributes['datecreated']);
			}
			return $this->attributes['datecreated'];
		}
		return $this->getUserUsername()->datecreated;
	}

	/**
	 * Get user's email address
	 * 
	 * If not set, it assumes the same domain as the mail "From address" config option
	 *
	 * @return  string
	 */
	public function getEmailAttribute()
	{
		return $this->getUserUsername()->email;
		/*if (!isset($this->attributes['email']))
		{
			$host = config('mail.from.address');
			$this->attributes['email'] = $this->username . strstr($host, '@');
		}
		return $this->attributes['email'];*/
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  string
	 */
	public function getLastVisitAttribute()
	{
		if (isset($this->attributes['lastseen']))
		{
			if (!($this->attributes['lastseen'] instanceof Carbon))
			{
				$this->attributes['lastseen'] = Carbon::parse($this->attributes['lastseen']);
			}
			return $this->attributes['lastseen'];
		}
		return $this->getUserUsername()->datelastseen;
	}

	/**
	 * Get surname
	 *
	 * @return  string
	 */
	public function getSurnameAttribute()
	{
		$name = explode(' ', $this->name);
		//$surname = end($name);

		return array_pop($name);
	}

	/**
	 * Get given name
	 *
	 * @return  string
	 */
	public function getGivenNameAttribute()
	{
		$name = explode(' ', $this->name);
		$surname = array_pop($name);

		return implode(' ', $name);
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  array
	 */
	public function getAuthorisedViewLevels()
	{
		if (is_null($this->authLevels))
		{
			$this->authLevels = array();
		}

		if (empty($this->_authLevels))
		{
			$this->authLevels = Gate::getAuthorisedViewLevels($this->id);
		}

		return $this->authLevels;
	}

	/**
	 * Gets an array of the authorised user roles
	 *
	 * @return  array
	 */
	public function getAuthorisedRoles()
	{
		if (is_null($this->authRoles))
		{
			$this->authRoles = array();
		}

		if (empty($this->authRoles))
		{
			$this->authRoles = Gate::getRolesByUser($this->id);
		}

		return $this->authRoles;
	}

	/**
	 * Determine if the entity does not have a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function cant($ability, $arguments = [])
	{
		return ! $this->can($ability, $arguments);
	}

	/**
	 * Determine if the entity does not have a given ability.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	public function cannot($ability, $arguments = [])
	{
		return $this->cant($ability, $arguments);
	}

	/**
	 * Get notes
	 *
	 * @return  object
	 */
	public function notes()
	{
		return $this->hasMany(Note::class, 'user_id');
	}

	/**
	 * Get sessions
	 *
	 * @return  object
	 */
	public function sessions()
	{
		return $this->hasMany(Session::class, 'user_id');
	}

	/**
	 * Get access roles
	 *
	 * @return  object
	 */
	public function roles()
	{
		return $this->hasMany(Map::class, 'user_id');
	}

	/**
	 * Get groups
	 *
	 * @return  object
	 */
	public function groups()
	{
		return $this->hasMany('App\Modules\Groups\Models\Member', 'userid');
	}

	/**
	 * Get queues
	 *
	 * @return  object
	 */
	public function queues()
	{
		return $this->hasMany('App\Modules\Queues\Models\User', 'userid');
	}

	/**
	 * Get usernames
	 *
	 * @return  object
	 */
	public function usernames()
	{
		return $this->hasMany(UserUsername::class, 'userid');
	}

	/**
	 * Get facets
	 *
	 * @return  object
	 */
	public function facets()
	{
		return $this->hasMany(Facet::class, 'user_id');
	}

	/**
	 * Find a facet value
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  string
	 */
	public function facet($key, $default = null)
	{
		$facet = $this->facets->where('key', $key);

		return count($facet) ? $facet->first()->value : $default;
	}

	/**
	 * Find a facet value
	 *
	 * @param   string  $key
	 * @param   mixed   $default
	 * @return  string
	 */
	public function addFacet($key, $val, $access = 0, $locked = 0)
	{
		$facet = new Facet;
		$facet->user_id = $this->id;
		$facet->key     = $key;
		$facet->value   = $val;
		$facet->access  = $access;
		$facet->locked  = $locked;
		$facet->save();

		$this->facets->push($facet);
	}

	/**
	 * Finds a user by username
	 *
	 * @param   string  $username
	 * @param   bool    $includeTrashed
	 * @return  object
	 */
	public static function findByUsername($username, $includeTrashed = false)
	{
		/*if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			return self::findByEmail($username);
		}*/

		$query = UserUsername::query();

		if ($includeTrashed)
		{
			$query->withTrashed();
		}

		$username = $query
			->where('username', '=', $username)
			->orderBy('datecreated', 'asc')
			->first();

		return $username ? $username->user : new self;
	}

	/**
	 * Finds a user by email
	 *
	 * @param   string  $email
	 * @return  object
	 */
	public static function findByEmail($email)
	{
		$username = UserUsername::query()
			->where('email', '=', $email)
			->orderBy('datecreated', 'asc')
			->first();

		return $username ? $username->user : new self;
	}

	/**
	 * Finds a user by organization ID
	 *
	 * @param   integer  $organization_id
	 * @return  object
	 */
	public static function findByOrganizationId($organization_id)
	{
		return self::query()
			->where('puid', '=', $organization_id)
			->first();
	}

	/**
	 * Finds a user by activation token
	 *
	 * @param   string  $token
	 * @return  object
	 */
	public static function findByActivationToken($token)
	{
		$user = self::query()
			->where('activation', '=', $token)
			->first();

		return $user ?: new self;
	}

	/**
	 * Find users that are activated and non-blocked
	 *
	 * @param   object  $query  \Illuminate\Database\Eloquent\Builder
	 * @return  object  \Illuminate\Database\Eloquent\Builder
	 */
	public function scopeActive($query)
	{
		return $query->where('blocked', '=', 0)
			->where('activation', '>', 0);
	}

	/**
	 * Save the record
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function save(array $options = array())
	{
		// Allow an exception to be thrown.
		try
		{
			// Get any set access groups
			$roles = null;

			if (array_key_exists('newroles', $this->attributes))
			{
				$roles = $this->attributes['newroles'];
				unset($this->attributes['newroles']);
			}

			// Save record
			$result = parent::save($options);

			if (!$result)
			{
				throw new Exception($this->getError());
			}

			// Update access groups
			if ($roles && is_array($roles))
			{
				Map::destroyByUser($this->id);

				Map::addUserToRole($this->id, $roles);
			}
		}
		catch (Exception $e)
		{
			$this->addError($e->getMessage());

			$result = false;
		}

		return $result;
	}

	/**
	 * Create a new user from a username
	 *
	 * @param   string  $username
	 * @return  User
	 */
	public static function createFromUsername($username)
	{
		$user = self::findByUsername($username);

		if ($user && $user->id)
		{
			return $user;
		}

		event($event = new UserLookup(['username' => $username]));

		if (count($event->results))
		{
			$user = array_shift($event->results);
		}

		if (!$user)
		{
			$user = new self;
		}

		if ($user)
		{
			$user->name = $user->name ?: $username;
			$user->api_token = Str::random(60);

			$newUsertype = config('module.users.new_usertype');

			if (!$newUsertype)
			{
				$newUsertype = Role::findByTitle('Registered')->id;
			}

			$user->newroles = array($newUsertype);

			$userusername = $user->getUserUsername();

			if ($user->save())
			{
				if (!$userusername)
				{
					$userusername = new UserUsername;
				}
				$userusername->userid = $user->id;
				$userusername->username = $username;
				$userusername->save();
			}
		}

		return $user;
	}

	/**
	 * Get user avatar
	 *
	 * Retrieves thumbnail by default as it's the most used.
	 * Will retrieve full-size by setting $thumb to false
	 *
	 * @param   bool $thumb
	 * @return  string
	 */
	public function avatar($thumb = true)
	{
		$name = ($thumb ? 'thumb' : 'photo');

		/*$found = false;
		foreach (['jpg', 'png'] as $ext)
		{
			$file = 'users/' . $this->id . '/' . $name . '.' . $ext;
			$path = storage_path('app/public/' . $file);

			if (file_exists($path))
			{
				$found = true;
				break;
			}
		}

		if (!$found)
		{*/
			$file = 'users/' . $this->id . '/' . $name . '.png';
			$path = storage_path('app/public/' . $file);

			if (!file_exists($path))
			{
				$avatar = new LetterAvatar($this->name);
				$avatar->setSize($thumb ? 50 : 250);

				if (!$avatar->saveAs($path))
				{
					return asset('modules/users/images/' . $name . '.png');
				}
			}
		//}

		return asset('files/' . $file);
	}
}
