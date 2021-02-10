<?php
namespace App\Modules\Courses\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Modules\Courses\Composers\ProfileComposer;
use App\Modules\Courses\Console\EmailAdditionsCommand;
use App\Modules\Courses\Console\EmailRemovalsCommand;
use App\Modules\Courses\Console\SyncCommand;
use App\Modules\Courses\Listeners\UserCourses;

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
	public $name = 'courses';

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
		$this->registerCommands();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		if (is_dir(dirname(dirname(__DIR__))) . '/Users')
		{
			$this->app['events']->subscribe(new UserCourses);
		}
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	public function registerCommands()
	{
		$this->commands([
			EmailAdditionsCommand::class,
			EmailRemovalsCommand::class,
			SyncCommand::class
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

		/*View::composer(
			'users::site.profile', ProfileComposer::class
		);*/
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
