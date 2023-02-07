<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetDisplaying
{
	/**
	 * Asset object
	 *
	 * @var Asset
	 */
	public $asset;

	/**
	 * Content sections
	 *
	 * @var array
	 */
	private $sections;

	/**
	 * Active content section
	 *
	 * @var string|null
	 */
	private $active;

	/**
	 * Constructor
	 *
	 * @param  Asset $asset
	 * @param  string|null $active
	 * @return void
	 */
	public function __construct(Asset $asset, $active = null)
	{
		$this->asset = $asset;
		$this->active = $active;
		$this->sections = array();
	}

	/**
	 * Get the asset
	 *
	 * @return Asset
	 */
	public function getAsset(): Asset
	{
		return $this->asset;
	}

	/**
	 * Get the sections
	 *
	 * @return string
	 */
	public function getSections(): array
	{
		return $this->sections;
	}

	/**
	 * Get the active section
	 *
	 * @return string|null
	 */
	public function getActive()
	{
		return $this->active;
	}

	/**
	 * Add a section
	 *
	 * @param  string  $route
	 * @param  string  $name
	 * @param  bool    $active
	 * @param  string|null  $content
	 * @return void
	 */
	public function addSection($route, $name, $active = false, $content = null): void
	{
		$this->sections[] = array(
			'route'   => $route,
			'name'    => $name,
			'active'  => $active,
			'content' => $content,
		);
	}
}
