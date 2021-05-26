<?php

namespace App\Modules\Themes\Contracts;

use App\Modules\Themes\Entities\Theme;

interface ActivatorInterface
{
	/**
	 * Enables a module
	 *
	 * @param Theme $theme
	 */
	public function enable(Theme $theme): void;

	/**
	 * Disables a module
	 *
	 * @param Theme $theme
	 */
	public function disable(Theme $theme): void;

	/**
	 * Determine whether the given status same with a module status.
	 *
	 * @param Theme $theme
	 * @param bool $status
	 *
	 * @return bool
	 */
	public function hasStatus(Theme $theme, bool $status): bool;

	/**
	 * Set active state for a module.
	 *
	 * @param Theme $theme
	 * @param bool $active
	 */
	public function setActive(Theme $theme, bool $active): void;

	/**
	 * Sets a module status by its name
	 *
	 * @param  string $name
	 * @param  bool $active
	 */
	public function setActiveByName(string $name, bool $active): void;

	/**
	 * Deletes a module activation status
	 *
	 * @param  Theme $theme
	 */
	public function delete(Theme $theme): void;

	/**
	 * Deletes any module activation statuses created by this class.
	 */
	public function reset(): void;
}
