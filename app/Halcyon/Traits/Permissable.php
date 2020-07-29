<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Halcyon\Traits;

use App\Halcyon\Access\Asset;

/**
 * Error message bag for shared error handling logic
 */
trait Permissable
{
	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 */
	public function getAssetName()
	{
		$name = (new \ReflectionClass($this))->getShortName();

		return $name . '.' . $this->getPrimaryKey();
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * In tracking the assets a title is kept for each asset so that there is some context available in a unified access manager.
	 * Usually this would just return $this->title or $this->name or whatever is being used for the primary name of the row.
	 * If this method is not overridden, the asset name is used.
	 *
	 * @return  string  The string to use as the title in the asset table.
	 */
	public function getAssetTitle()
	{
		return $this->getAssetName();
	}

	/**
	 * Method to get the parent asset under which to register this one.
	 *
	 * By default, all assets are registered to the ROOT node with ID, which will default to 1 if none exists.
	 * An extended class can define a table and ID to lookup.  If the asset does not exist it will be created.
	 *
	 * @param   Table    $table  A Table object for the asset parent.
	 * @param   integer  $id     Id to look up
	 * @return  integer
	 */
	public function getAssetParentId(Model $model = null, $id = null)
	{
		// For simple cases, parent to the asset root.
		$assets = new Asset;
		$rootId = $assets->getRootId();

		if (!empty($rootId))
		{
			return $rootId;
		}

		return 1;
	}
}
