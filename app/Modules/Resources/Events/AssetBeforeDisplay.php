<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetBeforeDisplay
{
	/**
	 * Active section
	 *
	 * @var Asset
	 */
	private $asset;

	/**
	 * Constructor
	 *
	 * @param  Asset $asset
	 * @return void
	 */
	public function __construct(Asset $asset)
	{
		$this->asset = $asset;
	}

	/**
	 * Get the user
	 *
	 * @return Asset
	 */
	public function getAsset()
	{
		return $this->asset;
	}

	/**
	 * Get the user
	 *
	 * @param Asset $asset
	 * @return void
	 */
	public function setAsset(Asset $asset)
	{
		$this->asset = $asset;
	}
}
