<?php

namespace App\Modules\Search\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Search\Http\Controllers';

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
		return '';
	}

	/**
	 * @return string
	 */
	protected function getApiRoute(): string
	{
		return dirname(__DIR__) . '/Routes/api.php';
	}
}
