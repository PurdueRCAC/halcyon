<?php

namespace App\Modules\Status\Events;

use App\Modules\Resources\Models\Asset;

class StatusRetrieval
{
	/**
	 * Asset
	 * 
	 * @var object
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
