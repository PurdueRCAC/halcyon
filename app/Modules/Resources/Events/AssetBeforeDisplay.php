<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Asset;

class AssetBeforeDisplay
{
	/**
	 * Active section
	 *
	 * @var string
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
	 * @return string
	 */
	public function getAsset()
	{
		return $this->asset;
	}

	/**
	 * Get the user
	 *
	 * @return string
	 */
	public function setAsset(Asset $asset)
	{
		$this->asset = $asset;
	}
}
