<?php

namespace App\Modules\Themes\Publishing;

class AssetPublisher extends Publisher
{
	/**
	 * Determine whether the result message will shown in the console.
	 *
	 * @var bool
	 */
	protected $showMessage = false;

	/**
	 * Get destination path.
	 *
	 * @return string
	 */
	public function getDestinationPath(): string
	{
		return $this->repository->getAssetPath($this->theme->getLowerName());
	}

	/**
	 * Get source path.
	 *
	 * @return string
	 */
	public function getSourcePath(): string
	{
		return config('module.themes.paths.themes', app_path('Themes'));
	}
}
