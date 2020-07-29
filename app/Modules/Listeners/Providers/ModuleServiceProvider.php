<?php

namespace App\Modules\Listeners\Providers;

//use App\Modules\Listeners\Entities\ListenerManager;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Modules\Listeners\Models\Listener;

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
	public $name = 'listeners';

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

		$this->registerListeners();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
	}

	/**
	 * Register any events for your application.
	 *
	 * @return void
	 */
	public function registerListeners()
	{
		$query = Listener::where('enabled', 1)
			->where('type', '=', 'listener');

		if ($user = auth()->user())
		{
			$query->whereIn('access', $user->getAuthorisedViewLevels());
		}

		$listeners = $query
			->orderBy('ordering', 'asc')
			->get();

		foreach ($listeners as $listener)
		{
			$this->subscribeListener($listener);
		}
	}

	/**
	 * Get by name (real, eg 'Breadcrumbs' or folder, eg 'mod_breadcrumbs')
	 *
	 * @param   object  $listener
	 * @return  void
	 */
	protected function subscribeListener($listener)
	{
		//try
		//{
			//$path = '/Listeners/' . Str::studly($listener->folder) . '/' . Str::studly($listener->element);

			if (!$listener->path)
			{
				return;
			}

			//$cls = 'App' . str_replace('/', '\\', $path) . '\\' . Str::studly($listener->element);
			//$cls = 'App' . '\\Listeners\\' . Str::studly($listener->folder) . '\\' . Str::studly($listener->element) . '\\' . Str::studly($listener->element);
			$cls = $listener->className;

			$r = new \ReflectionClass($cls);

			foreach ($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
			{
				$name = $method->getName();

				if ($name == 'subscribe')
				{
					$this->app['events']->subscribe(new $cls);
					break;
				}

				if (substr(strtolower($name), 0, 6) == 'handle')
				{
					$event = lcfirst(substr($name, 6));

					$this->app['events']->listen($event, $cls . '@' . $name);
				}

				$this->app['config']->set('listeners.' . $listener->folder . '.' . $listener->element, $listener->options);
			}
		//}
		//catch (\Exception $e)
		//{
			// Listener not found
		//}
	}

	protected function old()
	{
		$files = $this->app['files']->glob(app_path() . '/Listeners/*/*/*.php');

		/*foreach ($files as $file)
		{
			$cls = substr($file, strlen(app_path()));
			$cls = str_replace(array('/', '.php'), array('\\', ''), $cls);
			$cls = 'App' . $cls;

			$r = new \ReflectionClass($cls);

			foreach ($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method)
			{
				$name = $method->getName();

				if ($name == 'subscribe')
				{
					Event::subscribe(new $cls);
					break;
				}

				if (substr(strtolower($name), 0, 6) == 'handle')
				{
					$event = lcfirst(substr($name, 6));

					Event::listen($event, $cls . '@' . $name);
				}
			}
		}*/
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('listener', function ($app)
		{
			return new ListenerManager($app['events']);
		});
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
	 * Register config.
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
		return ['listener'];
	}
}
