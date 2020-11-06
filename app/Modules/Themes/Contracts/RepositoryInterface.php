<?php

namespace App\Modules\Themes\Contracts;

use App\Modules\Themes\Exceptions\ThemeNotFoundException;
use App\Modules\Themes\Entities\Theme;

interface RepositoryInterface
{
	/**
	 * Get all themes.
	 *
	 * @return mixed
	 */
	public function all();

	/**
	 * Get cached themes.
	 *
	 * @return array
	 */
	public function getCached();

	/**
	 * Scan & get all available themes.
	 *
	 * @return array
	 */
	public function scan();

	/**
	 * Get themes as themes collection instance.
	 *
	 * @return \Nwidart\Modules\Collection
	 */
	public function toCollection();

	/**
	 * Get scanned paths.
	 *
	 * @return array
	 */
	public function getScanPaths();

	/**
	 * Get list of enabled themes.
	 *
	 * @return mixed
	 */
	public function allEnabled();

	/**
	 * Get list of disabled themes.
	 *
	 * @return mixed
	 */
	public function allDisabled();

	/**
	 * Get count from all themes.
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Get all ordered themes.
	 * @param string $direction
	 * @return mixed
	 */
	public function getOrdered($direction = 'asc');

	/**
	 * Get themes by the given status.
	 *
	 * @param int $status
	 *
	 * @return mixed
	 */
	public function getByStatus($status);

	/**
	 * Find a specific theme.
	 *
	 * @param $name
	 * @return Module|null
	 */
	public function find(string $name);

	/**
	 * Find all themes that are required by a theme. If the theme cannot be found, throw an exception.
	 *
	 * @param $name
	 * @return array
	 * @throws ModuleNotFoundException
	 */
	public function findRequirements($name): array;

	/**
	 * Find a specific theme. If there return that, otherwise throw exception.
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public function findOrFail(string $name);

	public function getThemePath($themeName);

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	public function getFiles();

	/**
	 * Get a specific config data from a configuration file.
	 * @param string $key
	 *
	 * @param string|null $default
	 * @return mixed
	 */
	public function config(string $key, $default = null);

	/**
	 * Get a theme path.
	 *
	 * @return string
	 */
	public function getPath() : string;

	/**
	 * Find a specific theme by its alias.
	 * @param string $alias
	 * @return Module|void
	 */
	public function findByAlias(string $alias);

	/**
	 * Boot the themes.
	 */
	public function boot(): void;

	/**
	 * Register the themes.
	 */
	public function register(): void;

	/**
	 * Get asset path for a specific theme.
	 *
	 * @param string $theme
	 * @return string
	 */
	public function assetPath(string $theme): string;

	/**
	 * Delete a specific theme.
	 * @param string $theme
	 * @return bool
	 * @throws \Nwidart\Modules\Exceptions\ModuleNotFoundException
	 */
	public function delete(string $theme): bool;

	/**
	 * Determine whether the given theme is activated.
	 * @param string $name
	 * @return bool
	 * @throws ModuleNotFoundException
	 */
	public function isEnabled(string $name) : bool;

	/**
	 * Determine whether the given theme is not activated.
	 * @param string $name
	 * @return bool
	 * @throws ModuleNotFoundException
	 */
	public function isDisabled(string $name) : bool;
}
