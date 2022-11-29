<?php

namespace App\Modules\Queues\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Queues\Console\EmailFreeAuthorizedCommand;
use App\Modules\Queues\Console\EmailFreeDeniedCommand;
use App\Modules\Queues\Console\EmailFreeRemovedCommand;
use App\Modules\Queues\Console\EmailFreeRequestedCommand;
use App\Modules\Queues\Console\EmailQueueAuthorizedCommand;
use App\Modules\Queues\Console\EmailQueueDeniedCommand;
use App\Modules\Queues\Console\EmailQueueRemovedCommand;
use App\Modules\Queues\Console\EmailQueueRequestedCommand;
use App\Modules\Queues\Console\EmailWelcomeClusterCommand;
use App\Modules\Queues\Console\EmailWelcomeFreeCommand;
use App\Modules\Queues\Console\EmailExpiredCommand;
use App\Modules\Queues\Console\FixStatusCommand;
use App\Modules\Queues\Console\StopCommand;
use App\Modules\Queues\Console\StartCommand;
use App\Modules\Queues\Listeners\ManageDefaultQos;
use App\Modules\Queues\Listeners\GetUserQueues;
use App\Modules\Queues\Listeners\RemoveMembershipsForDeletedUser;
use App\Modules\Queues\LogProcessors\QueueMemberships;
use App\Modules\Queues\LogProcessors\UserRequests;
use Nwidart\Modules\Facades\Module;

class QueuesServiceProvider extends ServiceProvider
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
	public $name = 'queues';

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

		$this->app['events']->subscribe(new RemoveMembershipsForDeletedUser);
		$this->app['events']->subscribe(new ManageDefaultQos);

		if (Module::isEnabled('users'))
		{
			$this->app['events']->subscribe(new GetUserQueues);
		}

		if (Module::isEnabled('history'))
		{
			\App\Modules\History\Models\Log::pushProcessor(new QueueMemberships);
			\App\Modules\History\Models\Log::pushProcessor(new UserRequests);
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
			EmailFreeAuthorizedCommand::class,
			EmailFreeDeniedCommand::class,
			EmailFreeRemovedCommand::class,
			EmailFreeRequestedCommand::class,
			EmailQueueAuthorizedCommand::class,
			EmailQueueDeniedCommand::class,
			EmailQueueRemovedCommand::class,
			EmailQueueRequestedCommand::class,
			EmailWelcomeClusterCommand::class,
			EmailWelcomeFreeCommand::class,
			EmailExpiredCommand::class,
			FixStatusCommand::class,
			StopCommand::class,
			StartCommand::class,
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
}
