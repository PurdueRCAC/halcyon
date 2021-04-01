<?php

namespace App\Modules\Resources\Events;

use App\Modules\Resources\Models\Asset;

class AssetDisplaying
{
	/**
	 * Active section
	 *
	 * @var string
	 */
	private $asset;

	/**
	 * Content sections
	 *
	 * @var string
	 */
	private $sections;

	/**
	 * Constructor
	 *
	 * @param  object $user
	 * @return void
	 */
	public function __construct(Asset $asset)
	{
		$this->asset = $asset;
		$this->sections = array();
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
	public function getSections()
	{
		return $this->sections;
	}

	/**
	 * Get the user
	 *
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
