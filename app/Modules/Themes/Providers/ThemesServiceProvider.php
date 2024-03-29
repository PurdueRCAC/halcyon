<?php

namespace App\Modules\Themes\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\View;
use App\Modules\Themes\Entities\ThemeManager;
use App\Modules\Themes\Console\InstallCommand;
use App\Modules\Themes\Console\DisableCommand;
use App\Modules\Themes\Console\EnableCommand;
use App\Modules\Themes\Console\PublishCommand;
use App\Modules\Themes\Console\SetupCommand;

class ThemesServiceProvider extends ServiceProvider
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
	public $name = 'themes';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot(): void
	{
		$this->registerTranslations();
		$this->registerConfig();
		$this->registerAssets();
		$this->registerViews();
		$this->registerConsoleCommands();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		//!app()->runningInConsole() && 
		if (!request()->wantsJson())
		{
			$manager = $this->app['themes'];

			//$themePaths = $manager->all();
			$client = $this->app['isAdmin'] ? 'admin' : 'site';

			//$theme = $this->app['config']->get('app.' . $client . '-theme', $client);

			$theme = $manager->findEnabledByType($client);

			if (!is_null($theme))
			{
				//$theme = $manager->find($theme);

				$manager->activate($theme);

				$this->publish($theme->getPath() . '/assets', $manager->getAssetPath($theme->getLowerName()));

				$this->publishes([
					$theme->getPath() . '/assets' => $manager->getAssetPath($theme->getName()),
				], 'public');
			}
		}

		if ($path = theme_path('views'))
		{
			// Add module view overrides
			$paths = array_merge([$path], config('view.paths', []));
			config()->set('view.paths', $paths);

			// Add mail view overrides, if present
			$path .= '/mail';
			if (is_dir($path))
			{
				$paths = array_merge([$path], config('mail.markdown.paths', []));
				config()->set('mail.markdown.paths', $paths);
			}
		}
	}

	/**
	 * Publish the assets
	 */
	public function publish(string $sourcePath, string $destinationPath): void
	{
		if (!$this->app['files']->isDirectory($sourcePath))
		{
			throw new \InvalidArgumentException("Source path does not exist : {$sourcePath}");
		}

		if (!$this->app['files']->isDirectory($destinationPath))
		{
			$this->app['files']->makeDirectory($destinationPath, 0775, true);
		}

		foreach ($this->app['files']->allFiles($sourcePath) as $file)
		{
			$dest = str_replace($sourcePath, $destinationPath, $file);

			if (!$this->app['files']->exists($dest)
			 || $this->app['files']->lastModified($file) > $this->app['files']->lastModified($dest))
			{
				if (!$this->app['files']->exists(dirname($dest)))
				{
					$this->app['files']->makeDirectory(dirname($dest), 0775, true);
				}

				$this->app['files']->copy($file, $dest);
			}
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->singleton('themes', function ($app)
		{
			$path = $app['config']->get('module.themes.path', app_path('Themes'));

			return new ThemeManager($app, $path);
		});
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	protected function registerConsoleCommands(): void
	{
		$this->commands([
			InstallCommand::class,
			DisableCommand::class,
			EnableCommand::class,
			PublishCommand::class,
			SetupCommand::class,
		]);
	}

	/**
	 * Register config.
	 *
	 * @return void
	 */
	protected function registerConfig(): void
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
	protected function registerAssets(): void
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
	public function registerViews(): void
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
	public function registerTranslations(): void
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
	 * @return array<int,string>
	 */
	public function provides()
	{
		return ['themes'];
	}
}
