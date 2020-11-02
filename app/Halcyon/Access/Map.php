<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Access;

use Illuminate\Database\Eloquent\Model;

/**
 * User/Role map
 */
class Map extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'user_role_map';

	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'user_id';

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
		'user_id'  => 'positive|nonzero',
		'role_id' => 'positive|nonzero'
	);

	/**
	 * Defines a relationship to the User/Role Map
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo('App\Modules\Users\Model\User', 'user_id')->withDefault();
	}

	/**
	 * Defines a relationship to the User/Role Map
	 *
	 * @return  object
	 */
	public function role()
	{
		return $this->belongsTo(Role::class, 'role_id')->withDefault();
	}

	/**
	 * Delete this object and its dependencies
	 *
	 * @return  boolean
	 */
	public function delete($options = [])
	{
		$result = $this->newQuery()
			//->table($this->getTable())
			->where('role_id', '=', $this->role_id)
			->where('user_id', '=', $this->user_id)
			->delete();

		if (!$result)
		{
			$this->addError($query->getError());
			return false;
		}

		return true;
	}

	/**
	 * Delete objects of this type by Role ID
	 *
	 * @param   mixed    $role_id  Integer or array of integers
	 * @return  boolean
	 */
	public static function destroyByRole($role_id)
	{
		$role_id = (is_array($role_id) ? $role_id : array($role_id));

		$blank = new self();

		$result = $blank->newQuery()
			//->table($blank->getTable())
			->whereIn('role_id', $role_id)
			->delete();

		if (!$result)
		{
			return false;
		}

		return true;
	}

	/**
	 * Delete objects of this type by User ID
	 *
	 * @param   mixed    $user_id  Integer or array of integers
	 * @return  boolean
	 */
	public static function destroyByUser($user_id)
	{
		$user_id = (is_array($user_id) ? $user_id : array($user_id));

		$blank = new self();

		$result = $blank->getQuery()
			//->table($blank->getTable())
			->whereIn('user_id', $user_id)
			->delete();

		if (!$result)
		{
			return false;
		}

		return true;
	}

	/**
	 * Add a user to access roles
	 *
	 * @param   mixed    $user_id   Integer
	 * @param   mixed    $role_id  Integer or array of integers
	 * @return  boolean
	 */
	public static function addUserToRole($user_id, $role_id)
	{
		// Get the user's existing entries
		$entries = self::query()
			->where('user_id', '=', $user_id)
			->get();

		$existing = array();
		foreach ($entries as $entry)
		{
			$existing[] = $entry->role_id;
		}

		$role_id = (is_array($role_id) ? $role_id : array($role_id));

		$blank = new self();

		// Loop through roles to be added
		foreach ($role_id as $role)
		{
			$role = intval($role);

			// Is the role already an existing entry?
			if (in_array($role, $existing))
			{
				// Skip.
				continue;
			}

			$result = $blank->newQuery()
				->insert(array(
					'user_id'  => $user_id,
					'role_id' => $role
				));

			if (!$result)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Remove a user from an access roles
	 *
	 * @param   mixed    $user_id   Integer
	 * @param   mixed    $role_id  Integer or array of integers
	 * @return  boolean
	 */
	public static function removeUserFromRole($user_id, $role_id)
	{
		$role_id = (is_array($role_id) ? $role_id : array($role_id));

		$blank = new self();

		$result = $blank->newQuery()
			->where('user_id', '=', $user_id)
			->whereIn('role_id', $role_id)
			->delete();

		if (!$result)
		{
			return false;
		}

		return true;
	}
}
