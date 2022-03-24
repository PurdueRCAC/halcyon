<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetIsRendering
{
	/**
	 * The rendered body of the page
	 *
	 * @var string
	 */
	private $body;

	/**
	 * The original body of the page to render
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
		$this->body = '';
		$this->asset = $asset;
	}

	/**
	 * Get the page body
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Set the body
	 *
	 * @param string $body
	 * @return void
	 */
	public function setBody($body)
	{
		$this->body = $body;
	}

	/**
	 * Get Asset object
	 *
	 * @return Asset
	 */
	public function getAsset()
	{
		return $this->asset;
	}

	/**
	 * To string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getBody();
	}
}
