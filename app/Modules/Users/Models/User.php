<?php
namespace App\Modules\Users\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 *
 * @property int    $id
 * @property string $name
 * @property int    $puid
 * @property string $api_token
 * @property string $password
 * @property int    $enabled
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
	 * @var string
	 **/
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
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
	 * @var array<int,string>
	 */
	protected $appends = [
		'username',
		'unixid',
		'datecreated',
		'dateremoved',
		'email'
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int,string>
	 */
	protected $hidden = [
		'api_token',
		'password'
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
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
	 * @var UserUsername
	 */
	protected $userusername = null;

	/**
	 * Is this the a core admin
	 *
	 * @var null|bool
	 **/
	protected $isRoot;

	/**
	 * List of authorized view levels for this user
	 *
	 * @var null|array<int,int>
	 **/
	protected $authLevels;

	/**
	 * List of authorized roles for this user
	 *
	 * @var null|array<int,int>
	 **/
	protected $authRoles;

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
			$rootUser = config('module.users.root_user');

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
	public function trashed(): bool
	{
		return $this->getUserUsername()->trashed();
	}

	/**
	 * If user created timestamp is set
	 *
	 * @return  bool
	 **/
	public function isCreated(): bool
	{
		return !is_null($this->getUserUsername()->datecreated);
	}

	/**
	 * If user has an active session
	 *
	 * @return  bool
	 **/
	public function isOnline(): bool
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
	 * @return  UserUsername
	 **/
	public function getUserUsername(): UserUsername
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
	public function hasVisited(): bool
	{
		return !is_null($this->getUserUsername()->datelastseen);
	}

	/**
	 * Generate permissions for the modules provided
	 *
	 * @param  array<int,string> $names List of module names
	 * @return void
	 */
	public function setModulePermissionsAttribute(array $names): void
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
	 * @return  int
	 */
	public function getUnixidAttribute(): ?int
	{
		return $this->getUserUsername()->unixid;
	}

	/**
	 * Gets date created
	 *
	 * @return  Carbon|null
	 */
	public function getDatelastseenAttribute()
	{
		return $this->getLastVisitAttribute();
	}

	/**
	 * Gets date created
	 *
	 * @return  Carbon|null
	 */
	public function getDatecreatedAttribute()
	{
		return $this->getCreatedAtAttribute();
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  Carbon|null
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
	 * @return  string|null
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
	 * @return  Carbon|null
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
	 * @return  string|null
	 */
	public function getEmailAttribute()
	{
		return $this->getUserUsername()->email;
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  Carbon|null
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
	 * Set name value
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setNameAttribute(string $value): void
	{
		$value = trim($value);

		$this->attributes['name'] = $value;
	}

	/**
	 * Get surname
	 *
	 * @return  string
	 */
	public function getSurnameAttribute(): string
	{
		$name = trim($this->name);
		$name = explode(' ', $name);

		return array_pop($name);
	}

	/**
	 * Get given name
	 *
	 * @return  string
	 */
	public function getGivenNameAttribute(): string
	{
		$name = trim($this->name);
		$name = explode(' ', $name);
		$surname = array_pop($name);

		return implode(' ', $name);
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return  array<int,int>
	 */
	public function getAuthorisedViewLevels()
	{
		if (is_null($this->authLevels))
		{
			$this->authLevels = array();
		}

		if (empty($this->_authLevels))
		{
			$this->authLevels = array_values(Gate::getAuthorisedViewLevels($this->id));
		}

		return $this->authLevels;
	}

	/**
	 * Gets an array of the authorised user roles
	 *
	 * @return  array<int,int>
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
	public function cant($ability, $arguments = []): bool
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
	public function cannot($ability, $arguments = []): bool
	{
		return $this->cant($ability, $arguments);
	}

	/**
	 * Get notes
	 *
	 * @return  HasMany
	 */
	public function notes(): HasMany
	{
		return $this->hasMany(Note::class, 'user_id');
	}

	/**
	 * Get sessions
	 *
	 * @return  HasMany
	 */
	public function sessions(): HasMany
	{
		return $this->hasMany(Session::class, 'user_id');
	}

	/**
	 * Get access roles
	 *
	 * @return  HasMany
	 */
	public function roles(): HasMany
	{
		return $this->hasMany(Map::class, 'user_id');
	}

	/**
	 * Get groups
	 *
	 * @return  HasMany
	 */
	public function groups(): HasMany
	{
		return $this->hasMany('App\Modules\Groups\Models\Member', 'userid');
	}

	/**
	 * Get queues
	 *
	 * @return  HasMany
	 */
	public function queues(): HasMany
	{
		return $this->hasMany('App\Modules\Queues\Models\User', 'userid');
	}

	/**
	 * Get usernames
	 *
	 * @return  HasMany
	 */
	public function usernames(): HasMany
	{
		return $this->hasMany(UserUsername::class, 'userid');
	}

	/**
	 * Get facets
	 *
	 * @return  HasMany
	 */
	public function facets(): HasMany
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
	 * @param   mixed   $val
	 * @param   int $access
	 * @param   int $locked
	 * @return  self
	 */
	public function addFacet($key, $val, $access = 0, $locked = 0): self
	{
		$facet = new Facet;
		$facet->user_id = $this->id;
		$facet->key     = $key;
		$facet->value   = $val;
		$facet->access  = $access;
		$facet->locked  = $locked;
		$facet->save();

		$this->facets->push($facet);

		return $this;
	}

	/**
	 * Finds a user by username
	 *
	 * @param   string  $username
	 * @param   bool    $includeTrashed
	 * @return  User
	 */
	public static function findByUsername($username, $includeTrashed = false)
	{
		if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			return self::findByEmail($username);
		}

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
	 * @return  User
	 */
	public static function findByEmail($email)
	{
		$username = UserUsername::query()
			->where('email', '=', $email)
			->orderBy('username', 'asc')
			->orderBy('datecreated', 'asc')
			->first();

		return $username ? $username->user : new self;
	}

	/**
	 * Finds a user by organization ID
	 *
	 * @param   int  $organization_id
	 * @return  object|null
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
	 * @return  User
	 */
	public static function findByActivationToken($token)
	{
		$user = self::query()
			->where('activation', '=', $token)
			->first();

		return $user ?: new self;
	}

	/**
	 * Method to return a list of user Ids contained in a Role
	 *
	 * @param   int   $roleId     The role Id
	 * @param   bool  $recursive  Recursively include all child roles (optional)
	 * @return  array<int,int>
	 */
	public static function findByRole($roleId, $recursive = false): array
	{
		$test = $recursive ? '>=' : '=';

		// First find the users contained in the role
		$db = app('db');

		$result = $db->table('user_roles AS ug1')
			->select('DISTINCT(user_id)')
			->join('user_roles AS ug2', function($join) use ($test)
			{
				$join->on('ug2.lft', $test, 'ug1.lft')
					->on('ug1.rgt', $test, 'ug2.rgt');
			})
			->join('user_role_map AS m', 'm.role_id', 'ug2.id')
			->where('ug1.id', '=', $roleId)
			->pluck('user_id')
			->toArray();

		// Clean up any NULL values, just in case
		foreach ($result as $i => $v)
		{
			$result[$i] = (int) $v;
		}

		return $result;
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
	 * @param   array  $options
	 * @return  bool  False if error, True on success
	 */
	public function save(array $options = array()): bool
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

			if ($result)
			{
				// Update access groups
				if ($roles && is_array($roles))
				{
					Map::destroyByUser($this->id);

					Map::addUserToRole($this->id, $roles);
				}
			}
		}
		catch (Exception $e)
		{
			$result = false;
		}

		return $result;
	}

	/**
	 * Set default user role
	 *
	 * @return  self
	 */
	public function setDefaultRole(): User
	{
		$newUsertype = self::defaultRole();

		$this->newroles = array($newUsertype);

		return $this;
	}

	/**
	 * Get default user role
	 *
	 * @return  int
	 */
	public static function defaultRole(): int
	{
		$newUsertype = config('module.users.new_usertype');

		if (!$newUsertype)
		{
			$newUsertype = Role::findByTitle('Registered')->id;
		}

		return $newUsertype;
	}

	/**
	 * Create a new user from a username
	 *
	 * @param   string  $username
	 * @return  User
	 */
	public static function createFromUsername($username): User
	{
		$user = self::findByUsername($username);

		if ($user && $user->id)
		{
			return $user;
		}

		$email = null;
		$criteria = ['username' => $username];
		// Do we have an email?
		if (filter_var($username, FILTER_VALIDATE_EMAIL))
		{
			$email = $username;
			$criteria = ['email' => $email];
			$username = strstr($email, '@', true);

			// Horrible and hackish but try to avoid username collisions
			$exists = self::findByUsername($username);
			if ($exists)
			{
				$username = str_replace(['@', '.', '+'], '', $email);
			}
		}

		event($event = new UserLookup($criteria));

		if (count($event->results))
		{
			$user = array_shift($event->results);
		}

		if (!$user)
		{
			$user = new self;
		}

		$user->name = $user->name ?: $username;
		$user->api_token = Str::random(60);

		$user->setDefaultRole();

		$userusername = $user->getUserUsername();

		if ($user->save())
		{
			if (!$userusername)
			{
				$userusername = new UserUsername;
			}
			$userusername->userid = $user->id;
			if (!$userusername->username)
			{
				$userusername->username = $username;
			}
			if (!$userusername->email)
			{
				$userusername->email = $email;
			}
			$userusername->save();
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
	public function avatar($thumb = true): string
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
