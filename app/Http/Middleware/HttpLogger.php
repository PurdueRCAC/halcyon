<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Modules\History\Models\Log as Logger;

class HttpLogger
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle(Request $request, \Closure $next)
	{
		$response = $next($request);

		if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			return $response;
		}

		$app = 'ui';

		/*if (app()->has('isAdmin') && app()->get('isAdmin'))
		{
			$app = 'admin';
		}*/

		if ($request->segment(0) == 'api')
		{
			$app = 'api';
		}

		$action = Route::currentRouteAction();
		$cls = $action;
		$method = '';
		if (strstr($action, '@'))
		{
			$action = explode('@', $action);
			$cls = array_shift($action);
			$method = array_pop($action);
		}

		$cls = explode('\\', $cls);
		$cls = end($cls);

		$log = new Logger();
		$log->userid = (auth()->user() ? auth()->user()->id : 0);
		$log->transportmethod = $request->method();
		$log->hostname = $this->getClientHost();
		$log->servername = $this->getClientServer();
		$log->ip = $request->ip();
		// Handle some localhost cases
		$log->ip = $log->ip == '::1' ? '127.0.0.1' : $log->ip;
		if ($log->transportmethod == 'GET' && ($app == 'ui' || $app == 'admin'))
		{
			// Collect the user agent string
			$log->payload = $request->server('HTTP_USER_AGENT');
			$log->uri = '/' . $request->path();
		}
		else
		{
			$all = $request->all();
			foreach ($all as $k => $v)
			{
				if (is_string($v) && strlen($v) > 500)
				{
					$all[$k] = substr($v, 0, 500) . ' ...';
				}
			}
			$log->payload = json_encode($all);
			$log->uri = $request->fullUrl();
		}
		$log->payload = $log->payload ?: '';

		$log->status = 200;
		if (!($response instanceof StreamedResponse))
		{
			$log->status = $response->status();
		}
		
		$log->app = $app;
		$log->classname = $cls;
		$log->classmethod = $method;
		$log->objectid = '';
		$log->save();

		return $response;
	}

	/**
	 * Get client server name
	 *
	 * @return  string
	 */
	private function getClientServer(): string
	{
		$servername = '';

		if (isset($_SERVER['SERVER_NAME']))
		{
			$servername = $_SERVER['SERVER_NAME'];
		}
		elseif (isset($_SERVER['HTTP_HOST']))
		{
			$servername = $_SERVER['HTTP_HOST'];
		}
		elseif (function_exists('gethostname'))
		{
			$servername = gethostname();
		}

		return $servername;
	}

	/**
	 * Get client server name
	 *
	 * @return  string
	 */
	private function getClientHost(): string
	{
		$hostname = '';

		if (isset($_SERVER['REMOTE_HOST']))
		{
			$hostname = $_SERVER['REMOTE_HOST'];
		}
		elseif (!isset($_SERVER['REMOTE_ADDR']))
		{
			if (function_exists('gethostname'))
			{
				$hostname = gethostname();
			}
		}

		return $hostname;
	}
}
