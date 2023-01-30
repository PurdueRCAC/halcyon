<?php

namespace App\Halcyon\Access;

use SimpleXMLElement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;

/**
 * Class that handles all access authorization routines.
 */
class Gate
{
	/**
	 * Array of view levels
	 *
	 * @var  array<int,array>
	 */
	protected static $viewLevels = array();

	/**
	 * Array of rules for the asset
	 *
	 * @var  array<string,Rules>
	 */
	protected static $assetRules = array();

	/**
	 * Array of user roles.
	 *
	 * @var  array
	 */
	protected static $userRoles = array();

	/**
	 * Array of user role paths.
	 *
	 * @var  array
	 */
	protected static $userRolePaths = array();

	/**
	 * Array of cached roles by user.
	 *
	 * @var  array
	 */
	protected static $rolesByUser = array();

	/**
	 * Method for clearing static caches.
	 *
	 * @return  void
	 */
	public static function clearStatics()
	{
		self::$viewLevels    = array();
		self::$assetRules    = array();
		self::$userRoles     = array();
		self::$userRolePaths = array();
		self::$rolesByUser   = array();
	}

	/**
	 * Method to check if a user is authorised to perform an action, optionally on an asset.
	 *
	 * @param   int  $userId  Id of the user for which to check authorisation.
	 * @param   string   $action  The name of the action to authorise.
	 * @param   mixed    $asset   Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @return  bool  True if authorised.
	 */
	public static function check($userId, $action, $asset = null)
	{
		// Sanitise inputs.
		$userId = (int) $userId;
		$action = trim((string)$action);

		if (strstr($action, ' '))
		{
			$parts = explode(' ', $action, 2);
			$action = $parts[0];
			$asset  = $parts[1];
		}

		$action = strtolower(preg_replace('#[\s\-]+#', '.', $action));
		$asset  = $asset ? strtolower(preg_replace('#[\s\-]+#', '.', trim((string)$asset))) : '';

		// Temporary shim to get permissions working
		$parts = explode('.', $asset);
		if (count($parts) > 1)
		{
			if (!is_numeric(end($parts)))
			{
				$asset = $parts[0];
			}
		}

		// Default to the root asset node.
		if (empty($asset))
		{
			$asset = Asset::getRootId();
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		// Get all roles against which the user is mapped.
		$identities = self::getRolesByUser($userId);
		array_unshift($identities, $userId * -1);

		return self::$assetRules[$asset]->allow($action, $identities);
	}

	/**
	 * Determine if the given ability should be granted for the current user.
	 *
	 * @param  int  $userId  Id of the user for which to check authorisation.
	 * @param  string   $action  The name of the action to authorise.
	 * @param  mixed    $asset   Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @return bool
	 */
	public static function authorize($userId, $action, $asset = null)
	{
		$result = false;

		// Check for the configuration file failsafe.
		$rootUser = config('root_user');

		// The root_user variable can be a numeric user ID or a username.
		if (is_numeric($rootUser) && $userId > 0 && $userId == $rootUser)
		{
			$result = true;
		}
		else
		{
			// Get all roles against which the user is mapped.
			$identities = self::getRolesByUser($userId);

			array_unshift($identities, $userId * -1);

			if (self::getAssetRules(1)->allow('admin', $identities))
			{
				$result = true;
			}
		}

		if (!$result)
		{
			$actions = explode('|', $action);

			foreach ($actions as $ability)
			{
				$result = self::check($userId, $ability, $asset);

				if ($result)
				{
					break;
				}
			}
		}

		return $result ? self::allow() : self::deny();
	}

	/**
	 * Create a new access response.
	 *
	 * @param  string  $message
	 * @return \Illuminate\Auth\Access\Response
	 */
	protected static function allow($message = '')
	{
		return new Response(true, $message);
	}

	/**
	 * Throws an unauthorized exception.
	 *
	 * @param  string  $message
	 * @return void
	 * @throws \Illuminate\Auth\Access\AuthorizationException
	 */
	protected static function deny($message = 'This action is unauthorized.')
	{
		throw new AuthorizationException($message);
	}

	/**
	 * Determine if the given ability should be granted for the current user.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	/*public static function allows($ability, $arguments = [])
	{
		return self::check($ability, $arguments);
	}*/

	/**
	 * Determine if the given ability should be denied for the current user.
	 *
	 * @param  string  $ability
	 * @param  array|mixed  $arguments
	 * @return bool
	 */
	/*public static function denies($ability, $arguments = [])
	{
		return ! self::allows($ability, $arguments);
	}*/

	/**
	 * Method to check if a role is authorised to perform an action, optionally on an asset.
	 *
	 * @param   int  $roleId  The path to the role for which to check authorisation.
	 * @param   string   $action   The name of the action to authorise.
	 * @param   mixed    $asset    Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @return  bool  True if authorised.
	 */
	public static function checkRole($roleId, $action, $asset = null)
	{
		// Sanitize inputs.
		$roleId = (int) $roleId;
		$action = strtolower(preg_replace('#[\s\-]+#', '.', trim($action)));
		$asset  = strtolower(preg_replace('#[\s\-]+#', '.', trim($asset)));

		// Temporary shim to get permissions working
		$parts = explode('.', $asset);
		if (count($parts) > 1)
		{
			if (!is_numeric(end($parts)))
			{
				$asset = $parts[0];
			}
		}

		// Get role path for role
		$rolePath = self::getRolePath($roleId);

		// Default to the root asset node.
		if (!$asset)
		{
			$asset = Asset::getRootId();
		}

		// Get the rules for the asset recursively to root if not already retrieved.
		if (empty(self::$assetRules[$asset]))
		{
			self::$assetRules[$asset] = self::getAssetRules($asset, true);
		}

		return self::$assetRules[$asset]->allow($action, $rolePath);
	}

	/**
	 * Gets the parent roles that a leaf role belongs to in its branch back to the root of the tree
	 * (including the leaf role id).
	 *
	 * @param   mixed  $roleId  An integer or array of integers representing the identities to check.
	 * @return  mixed  True if allowed, false for an explicit deny, null for an implicit deny.
	 */
	protected static function getRolePath($roleId)
	{
		// Preload all roles
		if (empty(self::$userRoles))
		{
			self::$userRoles = array();

			$roles = Role::query()
				->orderBy('lft', 'asc')
				->get();

			foreach ($roles as $role)
			{
				self::$userRoles[$role->id] = $role;
			}
		}

		// Make sure roleId is valid
		if (!array_key_exists($roleId, self::$userRoles))
		{
			return array();
		}

		// Get parent roles and leaf role
		if (!isset(self::$userRolePaths[$roleId]))
		{
			self::$userRolePaths[$roleId] = array();

			foreach (self::$userRoles as $role)
			{
				if ($role->lft <= self::$userRoles[$roleId]->lft
				 && $role->rgt >= self::$userRoles[$roleId]->rgt)
				{
					self::$userRolePaths[$roleId][] = $role->id;
				}
			}
		}

		return self::$userRolePaths[$roleId];
	}

	/**
	 * Method to return the Rules object for an asset.  The returned object can optionally hold
	 * only the rules explicitly set for the asset or the summation of all inherited rules from
	 * parent assets and explicit rules.
	 *
	 * @param   mixed    $asset      Integer asset id or the name of the asset as a string.
	 * @param   bool  $recursive  True to return the rules object with inherited rules.
	 * @return  object   Rules object for the asset.
	 */
	public static function getAssetRules($asset, $recursive = false)
	{
		// Build the database query to get the rules for the asset.
		$model = new Asset();

		$db = app('db');

		$query = $db->table($model->getTable() . ' AS a')
			->select($recursive ? 'b.rules' : 'a.rules');

		// If the asset identifier is numeric assume it is a primary key, else lookup by name.
		if (is_numeric($asset))
		{
			$query->where('a.id', '=', (int) $asset);
		}
		else
		{
			$query->where('a.name', '=', $asset);
		}

		// If we want the rules cascading up to the global asset node we need a self-join.
		if ($recursive)
		{
			//$query->joinRaw($model->getTable() . ' AS b', 'b.lft <= a.lft AND b.rgt >= a.rgt', 'left');
			$query->leftJoin($model->getTable() . ' AS b', function($join)
			{
				$join->on('b.lft', '<=', 'a.lft')
					->on('b.rgt', '>=', 'a.rgt');
			});
			$query->orderBy('b.lft', 'asc');
		}

		if ($recursive)
		{
			$query->groupBy('b.id');
			$query->groupBy('b.rules');
			$query->groupBy('b.lft');
		}
		else
		{
			$query->groupBy('a.id');
			$query->groupBy('a.rules');
			$query->groupBy('a.lft');
		}

		$result = $query->get()->pluck('rules')->toArray();

		// Get the root even if the asset is not found and in recursive mode
		if (empty($result) && $recursive)
		{
			$result = Asset::findOrFail(Asset::getRootId());
			$result = [$result->rules];
		}

		// Instantiate and return the Rules object for the asset rules.
		$rules = new Rules;
		$rules->mergeCollection($result);

		return $rules;
	}

	/**
	 * Method to return a list of user roles mapped to a user. The returned list can optionally hold
	 * only the roles explicitly mapped to the user or all roles both explicitly mapped and inherited
	 * by the user.
	 *
	 * @param   int  $userId     Id of the user for which to get the list of roles.
	 * @param   bool  $recursive  True to include inherited user roles.
	 * @return  array    List of user role ids to which the user is mapped.
	 */
	public static function getRolesByUser($userId, $recursive = true)
	{
		// Creates a simple unique string for each parameter combination:
		$storeId = $userId . ':' . (int) $recursive;

		if (!isset(self::$rolesByUser[$storeId]))
		{
			// Guest user (if only the actually assigned role is requested)
			if (empty($userId) && !$recursive)
			{
				$result = array(config('users.guest_userrole', 1));
			}
			// Registered user and guest if all roles are requested
			else
			{
				$db = app('db');

				if (empty($userId))
				{
					$query = $db->table('user_roles AS a')
						->select($recursive ? 'b.id' : 'a.id')
						->where('a.id', '=', (int) config('users.guest_role', 1));
				}
				else
				{
					$query = $db->table('user_role_map AS map')
						->select($recursive ? 'b.id' : 'a.id')
						->where('map.user_id', '=', (int) $userId)
						->leftJoin('user_roles AS a', 'a.id', 'map.role_id');
				}

				// If we want the rules cascading up to the global asset node we need a self-join.
				if ($recursive)
				{
					$query->leftJoin('user_roles AS b', function($join)
					{
						$join->on('b.lft', '<=', 'a.lft')
							->on('b.rgt', '>=', 'a.rgt');
					});
				}

				$result = $query->pluck('id')->toArray();

				// Clean up any NULL or duplicate values, just in case
				foreach ($result as $i => $v)
				{
					$result[$i] = (int) $v;
				}

				if (empty($result))
				{
					$result = array(1);
				}
				else
				{
					$result = array_unique($result);
				}
			}

			self::$rolesByUser[$storeId] = $result;
		}

		return self::$rolesByUser[$storeId];
	}

	/**
	 * Method to return a list of user Ids contained in a Role
	 *
	 * @param   int  $roleId    The role Id
	 * @param   bool  $recursive  Recursively include all child roles (optional)
	 * @return  array
	 * @todo    This method should move somewhere else
	 */
	public static function getUsersByRole($roleId, $recursive = false)
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
	 * Method to return a list of view levels for which the user is authorised.
	 *
	 * @param   int  $userId  Id of the user for which to get the list of authorised view levels.
	 * @return  array    List of view levels for which the user is authorised.
	 */
	public static function getAuthorisedViewLevels($userId)
	{
		// Get all roles that the user is mapped to recursively.
		$roles = self::getRolesByUser($userId);

		// Only load the view levels once.
		if (empty(self::$viewLevels))
		{
			// Build the view levels array.
			$levels = Viewlevel::all();

			foreach ($levels as $level)
			{
				if (is_string($level->rules))
				{
					self::$viewLevels[$level->id] = (array) json_decode($level->rules);
				}
				else
				{
					self::$viewLevels[$level->id] = (array) $level->rules;
				}
			}
		}

		// Initialise the authorised array.
		$authorised = array(1);

		// Find the authorised levels.
		foreach (self::$viewLevels as $level => $rule)
		{
			foreach ($rule as $id)
			{
				if (($id < 0) && (($id * -1) == $userId))
				{
					$authorised[] = $level;
					break;
				}
				// Check to see if the role is mapped to the level.
				elseif (($id >= 0) && in_array($id, $roles))
				{
					$authorised[] = $level;
					break;
				}
			}
		}

		return $authorised;
	}

	/**
	 * Method to return a list of actions from a file for which permissions can be set.
	 *
	 * @param   string  $file    The path to the XML file.
	 * @param   string  $section An optional xpath to search for the fields.
	 * @return  bool|array    False if case of error or the list of actions available.
	 */
	public static function getActionsFromFile($file, $section = 'module') //"/access/section[@name='module']/")
	{
		if (!is_file($file))
		{
			// If unable to find the file return false.
			return false;
		}

		$actions = include $file;

		// Else return the actions from the xml.
		return isset($actions[$section]) ? $actions[$section] : false;//self::getActionsFromData(self::getXml($file, true), $xpath);
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                   $xpath  An optional xpath to search for the fields.
	 * @return  bool|array   False if case of error or the list of actions available.
	 */
	public static function getActionsFromData($data, $xpath = "/access/section[@name='module']/")
	{
		// If the data to load isn't already an XML element or string return false.
		if (!($data instanceof SimpleXMLElement) && !is_string($data))
		{
			return false;
		}

		// Attempt to load the XML if a string.
		if (is_string($data))
		{
			$data = self::getXml($data, false);

			// Make sure the XML loaded correctly.
			if (!$data)
			{
				return false;
			}
		}

		// Initialise the actions array
		$actions = array();

		// Get the elements from the xpath
		$elements = $data->xpath($xpath . 'action[@name][@title][@description]');

		// If there some elements, analyse them
		if (!empty($elements))
		{
			foreach ($elements as $action)
			{
				// Add the action to the actions array
				$actions[] = (object) array(
					'name'        => (string) $action['name'],
					'title'       => (string) $action['title'],
					'description' => (string) $action['description']
				);
			}
		}

		// Finally return the actions array
		return $actions;
	}

	/**
	 * Reads an XML file or string.
	 *
	 * @param   string   $data    Full path and file name.
	 * @param   bool  $isFile  true to load a file or false to load a string.
	 * @return  mixed    SimpleXMLElement on success or false on error.
	 * @todo    This may go in a separate class - error reporting may be improved.
	 */
	public static function getXml($data, $isFile = true)
	{
		// Disable libxml errors and allow to fetch error information as needed
		libxml_use_internal_errors(true);

		if ($isFile)
		{
			// Try to load the XML file
			$xml = simplexml_load_file($data);
		}
		else
		{
			// Try to load the XML string
			$xml = simplexml_load_string($data);
		}

		return $xml;
	}
}
