<?php

namespace App\Halcyon\Auth;

use Illuminate\Database\Eloquent\Model;

/**
 * Authentication Domain
 */
class Domain extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'auth_domain';

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
		'authenticator' => 'notempty'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 **/
	public $always = array(
		'type'
	);

	/**
	 * Generates automatic authenticator field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticAuthenticator($data)
	{
		$alias = $data['authenticator'];
		$alias = strip_tags($alias);
		$alias = trim($alias);
		if (strlen($alias) > 255)
		{
			$alias = substr($alias . ' ', 0, 255);
			$alias = substr($alias, 0, strrpos($alias, ' '));
		}

		return preg_replace("/[^a-zA-Z0-9]/", '', strtolower($alias));
	}

	/**
	 * Generates automatic modified field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticType($data)
	{
		return (isset($data['type']) && $data['type'] ? $data['type'] : 'authentication');
	}

	/**
	 * Get associated links
	 *
	 * @return  object
	 */
	public function links()
	{
		return $this->hasMany(__NAMESPACE__ . '\\Link', 'auth_domain_id');
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
		else
		{
			$row = self::all()
				->where('type', '=', $this->type)
				->where('authenticator', '=', $this->authenticator)
				->where('domain', '=', $this->domain)
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
	 * Get a Domain instance
	 *
	 * @param   string  $type
	 * @param   string  $authenticator
	 * @param   string  $domain
	 * @return  mixed   Object on success, False on failure
	 */
	public static function getInstance($type, $authenticator, $domain)
	{
		$query = self::query()
			->where('type', '=', $type)
			->where('authenticator', '=', $authenticator);
		if ($domain)
		{
			$query->where('domain', '=', $domain);
		}
		$row = $query->first();

		if (!$row || !$row->id)
		{
			return false;
		}

		return $row;
	}

	/**
	 * Create a new instance and return it
	 *
	 * @param   string  $type
	 * @param   string  $authenticator
	 * @param   string  $domain
	 * @return  mixed
	 */
	public static function createInstance($type, $authenticator, $domain = null)
	{
		if (empty($type) || empty($authenticator))
		{
			return false;
		}

		$row = new self;
		$row->type = $type;
		$row->authenticator = $authenticator;
		if ($domain)
		{
			$row->domain = $domain;
		}
		$row->save();

		if (!$row->id)
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
		$hzad = self::find($id);

		if (!$hzad)
		{
			return false;
		}

		return $hzad;
	}

	/**
	 * Fine a specific record, or create it
	 * if not found
	 *
	 * @param   string  $type
	 * @param   string  $authenticator
	 * @param   string  $domain
	 * @return  mixed
	 */
	public static function find_or_create($type, $authenticator, $domain=null)
	{
		$query = self::query()
			->where('type', '=', $type)
			->where('authenticator', '=', $authenticator);
		if ($domain)
		{
			$query->where('domain', '=', $domain);
		}
		$row = $query->first();

		if (!$row || !$row->id)
		{
			$row = new self();
			$row->type = $type;
			$row->authenticator = $authenticator;
			if ($domain)
			{
				$row->domain = $domain;
			}
			$row->save();
		}

		if (!$row->id)
		{
			return false;
		}

		return $row;
	}
}
