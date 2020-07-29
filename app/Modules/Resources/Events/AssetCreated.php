<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Asset;

class AssetCreated
{
	/**
	 * @var Asset
	 */
	public $asset;

	/**
	 * Constructor
	 *
	 * @param Asset $asset
	 * @param array $data
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
