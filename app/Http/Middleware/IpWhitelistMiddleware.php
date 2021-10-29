<?php
namespace App\Http\Middleware;

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Session\Store;
use Illuminate\Contracts\Auth\Factory as Auth;
//use Symfony\Component\HttpFoundation\IpUtils;
use App\Modules\Users\Models\User;

class IpWhitelistMiddleware
{
	/**
	 * @var Authentication
	 */
	private $auth;

	/**
	 * @var SessionManager
	 */
	private $session;

	/**
	 * @var Request
	 */
	private $request;

	/**
	 * @var Redirector
	 */
	private $redirect;

	/**
	 * @var Application
	 */
	private $application;

	/**
	 * Constructor
	 *
	 * @param  Illuminate\Contracts\Auth\Factory $auth
	 * @param  Illuminate\Session\Store $session
	 * @param  Illuminate\Http\Request $request
	 * @param  Illuminate\Routing\Redirector $redirect
	 * @param  Illuminate\Foundation\Application $application
	 * @return mixed
	 */
	public function __construct(Auth $auth, Store $session, Request $request, Redirector $redirect, Application $application)
	{
		$this->auth = $auth;
		$this->session = $session;
		$this->request = $request;
		$this->redirect = $redirect;
		$this->application = $application;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle($request, \Closure $next)
	{
		if (!$this->auth->check())
		{
			$allowed = false;

			// Check explicit whitelisted IPs
			//if (IpUtils::checkIp($request->ip(), config('ws.whitelist', ['127.0.0.1'])))
			if (in_array($request->ip(), config('ws.whitelist', [])))
			{
				$allowed = true;
			}

			if (!$allowed)
			{
				// Check whitelisted IP ranges
				foreach (config('ws.ranges', []) as $range)
				{
					if (empty($range) || count($range) < 2)
					{
						continue;
					}

					if ($this->ipInRange($range[0], $range[1], $request->ip()))
					{
						$allowed = true;
						break;
					}
				}
			}

			if (!$allowed)
			{
				$this->application->abort(403, 'IP Restricted.');
			}

			$existUser = User::where('id', config('ws.user_id', 1))->first();

			if ($existUser)
			{
				\Auth::loginUsingId($existUser->id);
			}
		}

		return $next($request);
	}

	/**
	 * We need to be able to check if an ip_address in a particular range
	 *
	 * @param  string $lower
	 * @param  string $upper
	 * @param  string $ip_address
	 * @return bool
	 */
	private function ipInRange($lower, $upper, $ip_address)
	{
		// Get the numeric reprisentation of the IP Address with IP2long
		$min    = ip2long($lower);
		$max    = ip2long($upper);
		$needle = ip2long($ip_address);

		// Then it's as simple as checking whether the needle falls between the lower and upper ranges
		return ($needle >= $min && $needle <= $max);
	}
}
