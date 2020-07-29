<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Entities\Asset;

class AssetDeleted
{
	/**
	 * @var Asset
	 */
	public $asset;

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
}
