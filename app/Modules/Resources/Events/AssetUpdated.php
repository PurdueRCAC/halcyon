<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetUpdated
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
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	public function getAsset()
	{
		return $this->asset;
	}
}
