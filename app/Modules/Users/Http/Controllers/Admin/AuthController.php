<?php

namespace App\Modules\Users\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
//use App\Modules\Users\Models\User;

class AuthController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function login(Request $request)
	{
		$return = $request->input('return');

		$authenticators = [];
		/*$plugins = \Plugin::byType('authentication');

		foreach ($plugins as $p)
		{
			$pparams = new Registry($p->params);

			// Make sure it supports admin login
			if (!$pparams->get('admin_login', false))
			{
				continue;
			}

			// If it's the default plugin, don't include it in the list (we'll include it separately)
			if ($p->name == 'halcyon')
			{
				$site_display = $pparams->get('display_name', \Config::get('sitename'));
				$basic = true;
			}
			else
			{
				$display = $pparams->get('display_name', ucfirst($p->name));
				$authenticators[$p->name] = array('name' => $p->name, 'display' => $display);
			}
		}*/
		if (app()->has('cas'))
		{
			$cas = app('cas');

			if (!$cas->checkAuthentication())
			{
				return $cas->authenticate();
			}
			else
			{
				app('cas')->logout();
			}
		}

		return view('users::admin.auth.login', [
			'return' => $return,
			'authenticators' => $authenticators
		]);
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function authenticate(Request $request)
	{
		$request->validate([
			'email'    => 'required|email',
			'password' => 'required|min:3'
		]);

		$credentials = [
			'email'    => $request->input('email'),
			'password' => $request->input('password'),
		];

		$remember = (bool) $request->input('remember_me', false);

		$options = array(
			'authenticator' => false,
			// The minimum group
			'group'         => 'Public Backend',
			// Make sure users are not autoregistered
			'autoregister'  => false,
			// Set the access control action to check.
			'action'        => 'login.admin'
		);

		// If a specific authenticator is specified try to call the login method for that plugin
		if ($authenticator = $request->input('authenticator', false))
		{
			$className = 'App\\Listeners\\Auth\\' . ucfirst($authenticator) . '\\' . ucfirst($authenticator);

			if (class_exists($className))
			{
				if (method_exists($className, 'login'))
				{
					$myplugin = new $className(); //$this, (array)$plugin);

					$result = $myplugin->login($credentials, $options);

					if (isset($options['return']))
					{
						$return = $options['return'];
					}
				}

				$options['authenticator'] = $authenticator;
			}
		}

		if (!Auth::attempt($credentials, $remember))
		{
			return redirect()
				->back()
				->withInput()
				->withError('Authentication failed');
		}

		$route = app('session')->get('url.intended', route('admin.dashboard'));

		return redirect()
			->intended($route);
			//->withSuccess(trans('users::messages.successfully logged in'));
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @param  Request $request
	 * @return Response
	 */
	public function logout(Request $request)
	{
		Auth::logout();

		/*if (app()->has('cas'))
		{
			app('cas')->logout('', route('admin.login'));
		}*/

		return redirect()->route('admin.login');
	}
}
