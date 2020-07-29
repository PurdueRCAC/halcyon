<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Asset;

class AssetUpdating
{
	/**
	 * @var Asset
	 */
	private $asset;

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
	 * @return User
	 */
	public function getAsset()
	{
		return $this->asset;
	}
}
