<?php
namespace App\Listeners\Auth\CILogon;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(Login::class, self::class . '@handleAuthenticate');
		$events->listen(Authenticate::class, self::class . '@handleAuthenticate');
		$events->listen(Logout::class, self::class . '@handleLogout');
	}

	/**
	 * Get the CILogon object, instantiating it if need be
	 *
	 * @return  Provider
	 */
	protected function provider()
	{
		if (is_null($this->cilogon))
		{
			$config = [
				'clientId'     => config('listener.auth.cilogon.client_id'),
				'clientSecret' => config('listener.auth.cilogon.client_secret'),
				'redirectUri'  => route('callback')
			];

			$server = config('listener.auth.cilogon.server');

			if (in_array($server, ['test', 'dev']))
			{
				$config['server'] = $server;
			}

			$this->cilogon = new Provider($config);
		}

		return $this->cilogon;
	}

	/**
	 * Handle user login events.
	 * 
	 * @param  Login $event
	 * @return void
	 */
	public function handleLogin(Login $event)
	{
		$request = $event->request;

		if ($request->ajax() || $request->wantsJson())
		{
			abort(401, 'Unauthorized.');
		}

		$provider = $this->provider();

		if (!$provider)
		{
			return;
		}

		// If we don't have an authorization code then get one with all 
		// possible CILogon-specific scopes.
		$loginUrl = $provider->getAuthorizationUrl(array(
			'scope' => ['openid', 'email', 'profile', 'org.cilogon.userinfo']
		));

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
	public function handleAuthenticate(Authenticate $event)
	{
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

			$newUsertype = config('module.users.new_usertype');

			if (!$newUsertype)
			{
				$newUsertype = Role::findByTitle('Registered')->id;
			}

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

				if ($newUsertype)
				{
					$user->newroles = array($newUsertype);
				}

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
			if (!count($user->roles) && $newUsertype)
			{
				$user->newroles = array($newUsertype);
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
	public function handleLogout(Logout $event)
	{
		session()->invalidate();
		session()->regenerate();
	}
}
