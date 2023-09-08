<?php

namespace App\Modules\Themes\Contracts;

use Illuminate\Support\Collection;
use App\Modules\Themes\Exceptions\ThemeNotFoundException;
use App\Modules\Themes\Entities\Theme;

interface RepositoryInterface
{
	/**
	 * Scan & get all available themes.
	 *
	 * @return array<int,string>
	 */
	public function scan(): array;

	/**
	 * Get scan path.
	 *
	 * @return string
	 */
	public function getScanPath(): string;

	/**
	 * Get all themes.
	 *
	 * @return array<string,Theme>
	 */
	public function all(): array;

	/**
	 * Get list of enabled themes.
	 *
	 * @return array<string,Theme>
	 */
	public function allEnabled(): array;

	/**
	 * Get list of disabled themes.
	 *
	 * @return array<string,Theme>
	 */
	public function allDisabled(): array;

	/**
	 * Get themes as themes collection instance.
	 *
	 * @return Collection
	 */
	public function toCollection(): Collection;

	/**
	 * Get count from all themes.
	 *
	 * @return int
	 */
	public function count(): int;

	/**
	 * Find a specific theme.
	 *
	 * @param string $name
	 * @return Theme|null
	 */
	public function find(string $name): ?Theme;

	/**
	 * Find a specific theme. If there return that, otherwise throw exception.
	 *
	 * @param string $name
	 * @return Theme|null
	 * @throws ThemeNotFoundException
	 */
	public function findOrFail(string $name): ?Theme;

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	public function getFiles();

	/**
	 * Activate a theme. Activation can be done by the theme's name, or via a Theme object.
	 *
	 * @param string|Theme $theme
	 * @return void
	 * @throws ThemeNotFoundException
	 */
	public function activate($theme): void;

	/**
	 * get Active theme
	 *
	 * @return Theme|null
	 */
	public function getActiveTheme(): ?Theme;

	/**
	 * Get path to the active theme
	 *
	 * @param  string $path
	 * @return string
	 */
	public function getActiveThemePath(string $path = null): string;

	/**
	 * Return the theme assets path
	 *
	 * @param  string $theme
	 * @return string
	 */
	public function getAssetPath(string $theme): string;

	/**
	 * Delete a specific theme.
	 *
	 * @param string $theme
	 * @return bool
	 */
	public function delete(string $theme): bool;

	/**
	 * Determine whether the given theme is activated.
	 *
	 * @param string $name
	 * @return bool
	 * @throws ThemeNotFoundException
	 */
	public function isEnabled(string $name) : bool;

	/**
	 * Determine whether the given theme is not activated.
	 *
	 * @param string $name
	 * @return bool
	 * @throws ThemeNotFoundException
	 */
	public function isDisabled(string $name) : bool;
}
