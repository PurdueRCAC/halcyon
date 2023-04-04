<?php

namespace App\Halcyon\Cas;

use Illuminate\Support\ServiceProvider;

class CasServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../../config/config.php' => config_path('cas.php'),
		]);
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('cas', function ()
		{
			$config = config('cas');

			if (!strstr($config['cas_redirect_path'], 'authenticator='))
			{
				$config['cas_redirect_path'] .= strstr($config['cas_redirect_path'], '?') ? '&' : '?';
				$config['cas_redirect_path'] .= 'authenticator=cas';
			}

			return new CasManager($config);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('cas');
	}
}
