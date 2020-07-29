<?php
namespace App\Listeners\Auth\Pucas;

class Pucas
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  \Illuminate\Events\Dispatcher  $events
	 */
	/*public function subscribe($events)
	{
		$events->listen(
			'Illuminate\Auth\Events\Login',
			__CLASS__ . '@handleUserLogin'
		);

		$events->listen(
			'Illuminate\Auth\Events\Logout',
			__CLASS__ . '@handleUserLogout'
		);

		$events->listen(
			'App\Modules\Login\Events\LoginOptions',
			__CLASS__ . '@handleUserLogout'
		);
	}*/

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
	 * Login button
	 *
	 * @param   string  $client
	 * @param   string  $return
	 * @return  string
	 */
	public function handleLoginOptions($client = 'site', $return = null)
	{
		app('translator')->addNamespace('listeners.auth', __DIR__ . '/lang');

		$html = '<a class="pucas account" href="' . url('/login?authenticator=pucas' . ($return ? '&return=' . $return : '')) . '">';
			$html .= '<div class="signin">';
				$html .= trans('listeners.auth::pucas.sign_in');
			$html .= '</div>';
		$html .= '</a>';

		return $html;
	}
}
