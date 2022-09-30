<?php

namespace App\Modules\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Blade;
use App\Modules\Pages\Console\ImportStaffCommand;
use App\Modules\Pages\Listeners\RouteCollector;
use Nwidart\Modules\Facades\Module;

class PagesServiceProvider extends ServiceProvider
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
	public $name = 'pages';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		/*Blade::directive('file', function ($expression)
		{
			return "<?php echo asset('files/' . $expression); ?>";
		});

		Blade::directive('filesize', function ($expression)
		{
			return "<?php echo App\Halcyon\Utility\Number::formatBytes(filesize(storage_path($expression))); ?>";
		});*/

		$this->registerTranslations();
		$this->registerConfig();
		$this->registerAssets();
		$this->registerViews();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		$this->commands([
			ImportStaffCommand::class
		]);

		if (Module::isEnabled('menus'))
		{
			$this->app['events']->subscribe(new RouteCollector);
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
}
