<?php

namespace App\Modules\Core\Entities;

use App\Halcyon\Access\Asset;

/**
 * Class for removing module permissions
 */
class PermissionsRemover
{
	/**
	 * Module name
	 *
	 * @var  string
	 */
	protected $module;

	/**
	 * Constructor
	 *
	 * @param string $module
	 */
	public function __construct(string $module)
	{
		$this->module = $module;
	}

	/**
	 * Get permissions record for module
	 *
	 * @return Asset|null
	 */
	private function getAsset(): ?Asset
	{
		return Asset::findByName($this->module);
	}

	/**
	 * Method to remove child/sub permissions for a module
	 *
	 * @return bool
	 */
	public function removeChildren(): bool
	{
		$asset = $this->getAsset();

		if ($asset)
		{
			$children = Asset::query()
				->where('lft', '>', $asset->lft)
				->where('rgt', '<', $asset->rgt)
				->get();

			foreach ($children as $child)
			{
				if (!$child->delete())
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to remove all permissions for a module
	 *
	 * @return bool
	 */
	public function removeAll(): bool
	{
		if ($this->removeChildren())
		{
			$asset = $this->getAsset();

			if ($asset && !$asset->delete())
			{
				return false;
			}
		}

		return true;
	}
}
