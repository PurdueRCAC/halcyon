<?php
namespace App\Providers;

use App\Http\Pathway\Trail;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Breadcrumbs service provider
 */
class PathwayServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return  void
	 */
	public function register(): void
	{
		$this->app->singleton('pathway', function ($app)
		{
			return new Trail();
		});
	}
}
