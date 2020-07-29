<?php

namespace App\Listeners\Listeners\Contracts;

use App\Listeners\Listeners\Models\Listener;

interface ActivatorInterface
{
	/**
	 * Enables a listener
	 *
	 * @param Listener $listener
	 */
	public function enable(Listener $listener): void;

	/**
	 * Disables a listener
	 *
	 * @param Listener $listener
	 */
	public function disable(Listener $listener): void;

	/**
	 * Determine whether the given status same with a listener status.
	 *
	 * @param Listener $listener
	 * @param bool $status
	 *
	 * @return bool
	 */
	public function hasStatus(Listener $listener, bool $status): bool;

	/**
	 * Set active state for a listener.
	 *
	 * @param Listener $listener
	 * @param bool $active
	 */
	public function setActive(Listener $listener, bool $active): void;

	/**
	 * Sets a listener status by its name
	 *
	 * @param  string $name
	 * @param  bool $active
	 */
	public function setActiveByName(string $name, bool $active): void;

	/**
	 * Deletes a listener activation status
	 *
	 * @param  Listener $listener
	 */
	public function delete(Listener $listener): void;

	/**
	 * Deletes any listener activation statuses created by this class.
	 */
	public function reset(): void;
}
