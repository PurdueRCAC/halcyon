<?php

namespace App\Modules\Messages\Providers;

use App\Providers\RoutingServiceProvider as CoreRoutingServiceProvider;

class RouteServiceProvider extends CoreRoutingServiceProvider
{
	/**
	 * The root namespace to assume when generating URLs to actions.
	 * @var string
	 */
	protected $namespace = 'App\Modules\Messages\Http\Controllers';

	/**
	 * @return string
	 */
	protected function getSiteRoute(): string
	{
		return '';
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

	/**
	 * // [!] Legacy compatibility
	 * 
	 * @return string
	 */
	protected function getWsRoute(): string
	{
		return dirname(__DIR__) . '/Routes/ws.php';
	}
}
