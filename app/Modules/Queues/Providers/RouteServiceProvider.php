<?php

namespace App\Modules\Queues\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Queues\Http\Controllers';

	/**
	 * @return string
	 */
	protected function getSiteRoute()
	{
		return dirname(__DIR__) . '/Routes/site.php';
	}

	/**
	 * @return string
	 */
	protected function getAdminRoute()
	{
		return dirname(__DIR__) . '/Routes/admin.php';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute()
	{
		return dirname(__DIR__) . '/Routes/api.php';
	}
}
