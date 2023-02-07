<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetCreating
{
	/**
	 * @var Asset
	 */
	public $asset;

	/**
	 * Constructor
	 *
	 * @param Asset $asset
	 * @return void
	 */
	public function __construct(Asset $asset)
	{
		$this->asset = $asset;
	}

	/**
	 * Return the entity
	 *
	 * @return Asset
	 */
	public function getAsset(): Asset
	{
		return $this->asset;
	}
}
