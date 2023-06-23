<?php

namespace App\Modules\Orders\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Logout;
use Illuminate\Session\SessionManager;
use App\Modules\Orders\Console\RenewCommand;
use App\Modules\Orders\Console\EmailStatusCommand;
use App\Modules\Orders\Entities\Cart;
use App\Modules\Orders\Listeners\GroupOrders;
use App\Modules\Orders\Listeners\UserOrders;
use App\Modules\Orders\Listeners\RouteCollector;
use App\Modules\Orders\Listeners\SyncItemsToProduct;
use App\Modules\Orders\LogProcessors\Orders as OrdersProcessor;
use Nwidart\Modules\Facades\Module;

class OrdersServiceProvider extends ServiceProvider
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
	public $name = 'orders';

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

		$this->app['events']->subscribe(new SyncItemsToProduct);

		if (Module::isEnabled('groups'))
		{
			$this->app['events']->subscribe(new GroupOrders);
		}

		if (Module::isEnabled('users'))
		{
			$this->app['events']->subscribe(new UserOrders);
		}

		if (Module::isEnabled('menus'))
		{
			$this->app['events']->subscribe(new RouteCollector);
		}

		if (Module::isEnabled('history'))
		{
			\App\Modules\History\Models\Log::pushProcessor(new OrdersProcessor);
		}
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register(): void
	{
		$this->app->bind('cart', Cart::class);

		$this->app['events']->listen(Logout::class, function ()
		{
			if ($this->app['config']->get('module.orders.destroy_on_logout'))
			{
				$this->app->make(SessionManager::class)->forget('cart');
			}
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
			RenewCommand::class,
			EmailStatusCommand::class,
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
}
