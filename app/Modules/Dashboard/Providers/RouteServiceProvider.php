<?php

namespace App\Modules\Dashboard\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Dashboard\Http\Controllers';

	/**
	 * @return string
	 */
	protected function getSiteRoute()
	{
		return '';
	}

	/**
	 * @return string
	 */
	protected function getAdminRoute()
	{
		return dirname(__DIR__) . '/Http/adminRoutes.php';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute()
	{
		return '';
	}
}
