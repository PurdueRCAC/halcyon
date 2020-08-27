<?php

namespace App\Modules\Menus\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use App\Modules\Menus\Entities\Menu;
use App\Modules\Menus\Listeners\InstallModule;
use App\Halcyon\Config\Registry;

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
	public $name = 'menus';

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

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		//$this->parseRoute();

		$this->app['events']->subscribe(new InstallModule);
	}

	/*public function parseRoute()
	{
		//$name = app('route')->currentRouteName();
		$route = app('router')->current();
		var_dump($route); die('here');
		$route = trim($route, '/');

		$menu = app('menu');

		if (empty($route))
		{
			$item = $menu->getDefault();

			// if user not allowed to see default menu item then avoid notices
			if (is_object($item))
			{
				// Set the information in the request
				//$vars = $item->query;

				// Get the itemid
				//$vars['menuid'] = $item->id;
				app('request')->merge(['itemid' => $item->id]);

				// Set the active menu item
				$menu->setActive($item->id);
			}

			return true;
		}

		$items = array_reverse($menu->getMenu());

		$found           = false;
		$route_lowercase = strtolower($route);

		return true;
	}*/

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->singleton('menu.manager', function ($app)
		{
			return new Menu();
		});

		$this->app->singleton('menu', function($app)
		{
			/*$options = [
				'language_filter' => null,
				'language'        => null,
				'access'          => auth()->user() ? auth()->user()->getAuthorisedViewLevels() : array()
			];

			$options['db'] = $app['db'];

			if ($app->has('language.filter'))
			{
				$options['language_filter'] = $app->get('language.filter');
				$options['language']        = $app->get('language')->getTag();
			}*/
			$app['menu.manager']->set('access', auth()->user() ? auth()->user()->getAuthorisedViewLevels() : array());

			return $app['menu.manager']; //->menu($app['isAdmin'] ? 'admin' : 'site', $options);
		});

		$this->app->singleton('menu.params', function($app)
		{
			$params = new Registry();

			$menu = $app['menu']->getActive();

			if (is_object($menu))
			{
				$params = $menu->params;
			}
			/*elseif ($app->has('component'))
			{
				$temp = clone $app['component']->params('com_menus');
				$params->merge($temp);
			}*/

			return $params;
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
