<?php
namespace App\Modules\Core\Providers;

use App\Modules\Core\Http\Middleware\PublicPath;
use App\Modules\Core\Http\Middleware\LegacyFiles;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
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
	public $name = 'core';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		/*Blade::directive('editor', function ($expression) {
			return "<?php echo editor($expression); ?>";
		});*/

		$this->registerTranslations();
		$this->registerConfig();
		$this->registerAssets();
		$this->registerViews();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		// adding global middleware
		$kernel = $this->app->make('Illuminate\Contracts\Http\Kernel');
		$kernel->pushMiddleware(PublicPath::class);
		$kernel->pushMiddleware(LegacyFiles::class);
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

		\Illuminate\Pagination\LengthAwarePaginator::defaultView('core::pagination.bootstrap-4');
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
