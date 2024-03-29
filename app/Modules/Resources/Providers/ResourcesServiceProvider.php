<?php

namespace App\Modules\Resources\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use App\Modules\Resources\Console\EmailSchedulingCommand;
use App\Modules\Resources\Console\CopyCommand;
use App\Modules\Resources\Listeners\Groups;
use App\Modules\Resources\Listeners\Queues;
use App\Modules\Resources\Listeners\Subresources;
use App\Modules\Resources\Listeners\Users;
use App\Modules\Resources\Listeners\PageCollector;
use App\Modules\Resources\Listeners\RouteCollector;
use Nwidart\Modules\Facades\Module;

class ResourcesServiceProvider extends ServiceProvider
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
	public $name = 'resources';

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

		$this->app['events']->subscribe(new Subresources);
		$this->app['events']->subscribe(new Users);
		$this->app['events']->subscribe(new PageCollector);

		if (Module::isEnabled('queues'))
		{
			$this->app['events']->subscribe(new Queues);
		}

		if (Module::isEnabled('groups'))
		{
			$this->app['events']->subscribe(new Groups);
		}

		if (Module::isEnabled('menus'))
		{
			$this->app['events']->subscribe(new RouteCollector);
		}
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	protected function registerConsoleCommands()
	{
		$this->commands([
			EmailSchedulingCommand::class,
			CopyCommand::class,
		]);
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
		}, config('view.paths', [])), [$sourcePath]), $this->name);
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
}
