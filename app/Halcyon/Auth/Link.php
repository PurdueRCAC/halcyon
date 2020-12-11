<?php

namespace App\Halcyon\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Authentication Link
 */
class Link extends Model
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'auth';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'auth_link';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'auth_domain_id' => 'positive|nonzero',
		'username'       => 'notempty'
	);

	/**
	 * Defines a belongs to one relationship between entry and user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Halcyon\User\User', 'user_id');
	}

	/**
	 * Defines a belongs to one relationship between entry and user
	 *
	 * @return  object
	 */
	public function domain()
	{
		return $this->belongsTo(__NAMESPACE__ . '\\Domain', 'auth_domain_id');
	}

	/**
	 * Get associated data
	 *
	 * @return  object
	 */
	public function data()
	{
		return $this->hasMany(__NAMESPACE__ . '\\Link\\Data', 'link_id');
	}

	/**
	 * Read a record
	 *
	 * @return  boolean  True on success, False on failure
	 */
	public function read()
	{
		if ($this->id)
		{
			$row = self::find($this->id);
		}
		elseif ($this->user_id)
		{
			$row = self::query()
				->where('auth_domain_id', '=', $this->auth_domain_id)
				->where('user_id', '=', $this->user_id)
				->first();
		}
		elseif ($this->username)
		{
			$row = self::query()
				->where('auth_domain_id', '=', $this->auth_domain_id)
				->where('username', '=', $this->username)
				->first();
		}

		if (!$row || !$row->id)
		{
			return false;
		}

		foreach (array_keys($this->getAttributes()) as $key)
		{
			$this->{$key} = $row->{$key};
		}

		return true;
	}

	/**
	 * Create a record
	 *
	 * @return  boolean  True on success, False on failure
	 */
	/*public function create()
	{
		return $this->save();
	}*/

	/**
	 * Update a record
	 *
	 * @param   boolean  $all  Update all properties?
	 * @return  boolean
	 */
	/*public function update($all = false)
	{
		return $this->save();
	}*/

	/**
	 * Delete a record
	 *
	 * @return  boolean
	 */
	/*public function delete()
	{
		return $this->destroy();
	}*/

	/**
	 * Get an instance of a record
	 *
	 * @param   integer  $auth_domain_id
	 * @param   string   $username
	 * @return  mixed    Object on success, False on failure
	 */
	public static function getInstance($auth_domain_id, $username)
	{
		$row = self::query()
			->where('auth_domain_id', '=', $auth_domain_id)
			->where('username', '=', $username)
			->first();

		if (!$row || !$row->id)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Create a new instance and return it
	 *
	 * @param   integer  $auth_domain_id
	 * @param   string   $username
	 * @return  mixed
	 */
	public static function createInstance($auth_domain_id, $username)
	{
		if (empty($auth_domain_id) || empty($username))
		{
			return false;
		}

		$row = new self;
		$row->auth_domain_id = $auth_domain_id;
		$row->username = $username;
		$row->save();

		if (!$row->id)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Find existing auth_link entry, return false if none exists
	 *
	 * @param   string  $type
	 * @param   string  $authenticator
	 * @param   string  $domain
	 * @param   string  $username
	 * @return  mixed   object on success and false on failure
	 */
	public static function findBy($type, $authenticator, $domain, $username)
	{
		$hzad = Domain::find_or_create($type, $authenticator, $domain);

		if (!is_object($hzad))
		{
			return false;
		}

		if (empty($username))
		{
			return false;
		}

		$row = self::query()
			->where('auth_domain_id', '=', $hzad->id)
			->where('username', '=', $username)
			->first();

		if (!$row || !$row->id)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Find a record by ID
	 *
	 * @param   integer  $id
	 * @return  mixed    Object on success, False on failure
	 */
	public static function find_by_id($id)
	{
		$row = self::find($id);

		if (!$row)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Find a record, creating it if not found.
	 *
	 * @param   string  $type
	 * @param   string  $authenticator
	 * @param   string  $domain
	 * @param   string  $username
	 * @return  mixed   Object on success, False on failure
	 */
	public static function find_or_create($type, $authenticator, $domain, $username)
	{
		$hzad = Domain::find_or_create($type, $authenticator, $domain);

		if (!$hzad)
		{
			return false;
		}

		if (empty($username))
		{
			return false;
		}

		$row = self::query()
			->where('auth_domain_id', '=', $hzad->id)
			->where('username', '=', $username)
			->first();

		if (!$row || !$row->id)
		{
			$row = new self;
			$row->auth_domain_id = $hzad->id;
			$row->username = $username;
			$row->save();
		}

		if (!$row->id)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Return array of linked accounts associated with a given user id
	 * Also include auth domain name for easy display of domain name
	 *
	 * @param   integer  $user_id  ID of user to return accounts for
	 * @return  array    Array of auth link entries for the given user_id
	 */
	public static function find_by_user_id($user_id = null)
	{
		if (empty($user_id))
		{
			return false;
		}

		$l = self::blank()->getTableName();
		$d = Domain::blank()->getTableName();

		$results = self::query()
			->select([$l . '.*', $d . '.authenticator AS auth_domain_name'])
			->innerJoin($d, $d . '.id', $l . '.auth_domain_id')
			->where($l . '.user_id', '=', $user_id)
			->get();

		if (empty($results))
		{
			return false;
		}

		return $results->toArray();
	}

	/**
	 * Find trusted emails by User ID
	 *
	 * @param   integer  $user_id  USer ID
	 * @return  mixed
	 */
	public static function find_trusted_emails($user_id)
	{
		if (empty($user_id) || !is_numeric($user_id))
		{
			return false;
		}

		$results = self::query()
			->where('user_id', '=', $user_id)
			->get()
			->fieldsByKey('email');

		if (empty($results))
		{
			return false;
		}

		return $results;
	}

	/**
	 * Delete a record by User ID
	 *
	 * @param   integer  $user_id  User ID
	 * @return  boolean
	 */
	public static function delete_by_user_id($user_id = null)
	{
		if (empty($user_id))
		{
			return true;
		}

		$results = self::query()
			->where('user_id', '=', $user_id)
			->get();

		foreach ($results as $result)
		{
			if (!$result->destroy())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Return array of linked accounts associated with a given email address
	 * Also include auth domain name for easy display of domain name
	 *
	 * @param   string  $email
	 * @param   array   $exclude
	 * @return  mixed
	 */
	public static function find_by_email($email, $exclude = array())
	{
		if (empty($email))
		{
			return false;
		}

		$query = self::query()
			->where('email', '=', $email);

		if (!empty($exclude[0]))
		{
			foreach ($exclude as $e)
			{
				$query->where('auth_domain_id', '!=', $e);
			}
		}

		$rows = $query->get();

		$results = array();

		foreach ($rows as $row)
		{
			$result = $row->toArray();
			$result['auth_domain_name'] = $row->domain->authenticator;

			$results[] = $result;
		}

		if (empty($results))
		{
			return false;
		}

		return $results;
	}
}
