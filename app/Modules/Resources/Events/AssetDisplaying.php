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
	 * @var string
	 */
	private $sections;

	/**
	 * Active content section
	 *
	 * @var string
	 */
	private $active;

	/**
	 * Constructor
	 *
	 * @param  Asset $asset
	 * @param  string $active
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
	public function getAsset()
	{
		return $this->asset;
	}

	/**
	 * Get the sections
	 *
	 * @return string
	 */
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Get the active section
	 *
	 * @return string
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
	 * @param  string  $content
	 * @return void
	 */
	public function addSection($route, $name, $active = false, $content = null)
	{
		$this->sections[] = array(
			'route'   => $route,
			'name'    => $name,
			'active'  => $active,
			'content' => $content,
		);
	}
}
