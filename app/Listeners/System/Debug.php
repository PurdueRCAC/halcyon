<?php

namespace App\Listeners\System;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Debug
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 */
	public function subscribe($events)
	{
		$events->listen(
			'Illuminate\Auth\Events\Login',
			__CLASS__ . '@handleUserLogin'
		);

		$events->listen(
			'Illuminate\Auth\Events\Logout',
			__CLASS__ . '@handleUserLogout'
		);
	}

	/**
	 * Handle user login events.
	 */
	public function handleUserLogin($event)
	{
		// ...
	}

	/**
	 * Handle user logout events.
	 */
	public function handleUserLogout($event)
	{
		// ...
	}

	/**
	 * Handle user logout events.
	 */
	public function handleLoginOption($event)
	{
		// ...
	}
}
