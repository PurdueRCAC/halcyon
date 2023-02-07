<?php

namespace App\Modules\Knowledge\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Knowledge\Http\Controllers';

	/**
	 * @return string
	 */
	protected function getSiteRoute(): string
	{
		return dirname(__DIR__) . '/Routes/site.php';
	}

	/**
	 * @return string
	 */
	protected function getAdminRoute(): string
	{
		return dirname(__DIR__) . '/Routes/admin.php';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute(): string
	{
		return dirname(__DIR__) . '/Routes/api.php';
	}
}
