<?php
namespace App\Listeners\Auth\CILogon;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Events\Dispatcher;
use App\Modules\Users\Events\Authenticators;
use App\Modules\Users\Events\Login;
use App\Modules\Users\Events\Authenticate;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Halcyon\Access\Role;
use CILogon\OAuth2\Client\Provider\CILogon as Provider;

class CILogon
{
	/**
	 * CILogon provider
	 *
	 * @var Provider
	 */
	protected $cilogon = null;

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Dispatcher  $events
	 * @return void
	 */
	public function subscribe(Dispatcher $events): void
	{
		$events->listen(Authenticators::class, self::class . '@handleAuthenticators');
		$events->listen(Login::class, self::class . '@handleLogin');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
		$events->listen(Logout::class, self::class . '@handleLogout');
	}

	/**
	 * Get the CILogon object, instantiating it if need be
	 *
	 * @return  Provider
	 */
	protected function provider(): ?Provider
	{
		if (is_null($this->cilogon))
		{
			$config = config('listener.auth.cilogon', []);

			if (empty($config) || !$config['clientId'] || !$config['clientSecret'])
			{
				return null;
			}

			$config['redirectUri'] = route('callback');

			if (!in_array($config['server'], ['test', 'dev']))
			{
				$config['server'] = null;
			}

			$this->cilogon = new Provider($config);
		}

		return $this->cilogon;
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Authenticators $event
	 * @return void
	 */
	public function handleAuthenticators(Authenticators $event): void
	{
		app('translator')->addNamespace(
			'listener.auth.cilogon',
			__DIR__ . '/lang'
		);

		app('view')->addNamespace(
			'listener.auth.cilogon',
			__DIR__ . '/views'
		);

		$event->addAuthenticator('cilogon', [
			'label' => 'CILogon',
			'view' => 'listener.auth.cilogon::index',
		]);
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Login $event
	 * @return void
	 */
	public function handleLogin(Login $event): void
	{
		if ($event->authenticator != 'cilogon')
		{
			return;
		}

		$request = $event->request;

		if ($request->ajax() || $request->wantsJson())
		{
			abort(401, 'Unauthorized.');
		}

		$provider = $this->provider();

		if (!$provider)
		{
			abort(500, 'CILogon not configured.');
		}

		// If we don't have an authorization code then get one with all 
		// possible CILogon-specific scopes.
		$loginUrl = $provider->getAuthorizationUrl(array(
			'scope' => ['openid', 'email', 'profile', 'org.cilogon.userinfo']
		));
		$returnUrl = $request->input('return', route(config('module.users.redirect_route_after_login', 'home')));

		session()->put('cilogon.state', $provider->getState());
		session()->put('cilogon.returnUrl', $returnUrl);

		// Redirect to the login URL
		abort(redirect($loginUrl));
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Authenticate $event
	 * @return void
	 */
	public function handleAuthenticate(Authenticate $event): void
	{
		if ($event->authenticator != 'cilogon')
		{
			return;
		}

		$request = $event->request;

		$provider = $this->provider();

		if (!$provider)
		{
			return;
		}

		// Check given state against previously stored one to mitigate CSRF attack
		$storedState = session()->get('cilogon.state');
		$state = $request->input('state');

		if (empty($state) || $storedState !== $state)
		{
			throw new \Exception('Mismatched state', 401);
		}

		session()->forget('cilogon.state');

		if (!auth()->user())
		{
			// Try to get an access token using the authorization code grant
			$token = $provider->getAccessToken(
				'authorization_code',
				array('code' => $request->input('code'))
			);

			// Using the access token, get the user's details
			$cilogonResponse = $provider->getResourceOwner($token);

			$email = $cilogonResponse->getEmail();

			$user = User::findByEmail($email, config('module.users.restore_on_login', 0));

			// Create accounts on login?
			if ((!$user || !$user->id) && config('module.users.create_on_login', 1))
			{
				$user = new User;
				$user->name = $cilogonResponse->getName();
				if (empty($user->name))
				{
					$user->name = $cilogonResponse->getGivenName() . ' ' . $cilogonResponse->getFamilyName();
				}
				$user->api_token = Str::random(60);
				//$user->puid = $cilogonResponse->getId();

				$user->setDefaultRole();

				if ($user->save())
				{
					$userusername = new UserUsername;
					$userusername->userid = $user->id;
					$userusername->username = str_replace(['@', '.'], ['at', 'dot'], $email);
					$userusername->email = $email;
					$userusername->save();
				}
			}

			if (!$user || !$user->id)
			{
				abort(401, 'Unauthorized.');
			}

			// Restore "trashed" accounts on login?
			if ($user->trashed())
			{
				if (config('module.users.restore_on_login', 0))
				{
					abort(401, 'Unauthorized.');
				}

				$user->getUserUsername()->restore();
			}

			// Check for missing data
			if (!count($user->roles))
			{
				$user->setDefaultRole();
				$user->save();
			}

			if (!$user->api_token)
			{
				$user->api_token = Str::random(60);
				$user->save();
			}

			Auth::loginUsingId($user->id);

			$event->authenticated = true;
		}
	}

	/**
	 * Handle user logout events.
	 *
	 * @param  Logout $event
	 * @return void
	 */
	public function handleLogout(Logout $event): void
	{
		session()->invalidate();
		session()->regenerate();
	}
}
