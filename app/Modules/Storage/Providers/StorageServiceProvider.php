<?php

namespace App\Modules\Storage\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use App\Modules\Storage\Listeners\Messages;
use App\Modules\Storage\Listeners\Resources;
use App\Modules\Storage\Listeners\GroupMembers;
use App\Modules\Storage\Listeners\UnixGroupMembers;
use App\Modules\Storage\Listeners\Notifications;
use App\Modules\Storage\Listeners\UserStorage;
use App\Modules\Storage\Console\EmailQuotaCommand;
use App\Modules\Storage\Console\QuotaCheckCommand;

class StorageServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Module name
	 *
	 * @var string
	 */
	public $name = 'storage';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->registerAssets();
		$this->registerViews();
		$this->registerConsoleCommands();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		$this->app['events']->subscribe(new Notifications);

		if (is_dir(dirname(dirname(__DIR__))) . '/Messages')
		{
			$this->app['events']->subscribe(new Messages);
		}

		if (is_dir(dirname(dirname(__DIR__))) . '/Resources')
		{
			$this->app['events']->subscribe(new Resources);
		}

		if (is_dir(dirname(dirname(__DIR__))) . '/Groups')
		{
			$this->app['events']->subscribe(new GroupMembers);
			$this->app['events']->subscribe(new UnixGroupMembers);
		}

		if (is_dir(dirname(dirname(__DIR__))) . '/Users')
		{
			$this->app['events']->subscribe(new UserStorage);
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	protected function registerConsoleCommands()
	{
		$this->commands([
			EmailQuotaCommand::class,
			QuotaCheckCommand::class,
		]);
	}

	/**
	 * Register config.
	 *
	 * @return void
	 */
	protected function registerConfig()
	{
		$this->publishes([
			__DIR__ . '/../Config/config.php' => config_path('module/' . $this->name . '.php'),
		], 'config');

		$this->mergeConfigFrom(
			__DIR__ . '/../Config/config.php', $this->name
		);
	}

	/**
	 * Publish assets
	 *
	 * @return void
	 */
	protected function registerAssets()
	{
		$this->publishes([
			__DIR__ . '/../Resources/assets' => public_path() . '/modules/' . strtolower($this->name) . '/assets',
		], 'config');
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/' . $this->name);

		$sourcePath = __DIR__ . '/../Resources/views';

		$this->publishes([
			$sourcePath => $viewPath
		],'views');

		$this->loadViewsFrom(array_merge(array_map(function ($path)
		{
			return $path . '/modules/' . $this->name;
		}, config('view.paths')), [$sourcePath]), $this->name);
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$langPath = resource_path('lang/modules/' . $this->name);

		if (!is_dir($langPath))
		{
			$langPath = __DIR__ . '/../Resources/lang';
		}

		$this->loadTranslationsFrom($langPath, $this->name);
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}
}
