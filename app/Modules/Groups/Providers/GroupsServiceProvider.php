<?php

namespace App\Modules\Groups\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\View;
use App\Modules\Groups\Listeners\AddManagersToNewQueue;
use App\Modules\Groups\Listeners\AddUserToUnixGroup;
use App\Modules\Groups\Listeners\RemoveMembershipsForDeletedUser;
use App\Modules\Groups\Listeners\NotifyManagersOfUserRequest;
use App\Modules\Groups\Composers\ProfileComposer;
use App\Modules\Groups\Console\EmailAuthorizedCommand;
use App\Modules\Groups\Console\EmailRemovedCommand;
use App\Modules\Groups\Console\SyncMembershipCommand;
use App\Modules\Groups\Console\GroupAddMemberCommand;
use App\Modules\Groups\Console\GroupRemoveMemberCommand;
use App\Modules\Groups\Console\UnixGroupAddMemberCommand;
use App\Modules\Groups\Console\UnixGroupRemoveMemberCommand;
use App\Modules\Groups\LogProcessors\Groups;
use App\Modules\Groups\LogProcessors\GroupMemberships;
use App\Modules\Groups\LogProcessors\UnixGroupMemberships;
use App\Modules\Groups\LogProcessors\UnixGroups;
use Nwidart\Modules\Facades\Module;

class GroupsServiceProvider extends ServiceProvider
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
	public $name = 'groups';

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

		$this->app['events']->subscribe(new RemoveMembershipsForDeletedUser);
		$this->app['events']->subscribe(new NotifyManagersOfUserRequest);

		if (Module::isEnabled('queues'))
		{
			$this->app['events']->subscribe(new AddManagersToNewQueue);
			$this->app['events']->subscribe(new AddUserToUnixGroup);
		}

		if (Module::isEnabled('history'))
		{
			\App\Modules\History\Models\Log::pushProcessor(new Groups);
			\App\Modules\History\Models\Log::pushProcessor(new GroupMemberships);
			\App\Modules\History\Models\Log::pushProcessor(new UnixGroups);
			\App\Modules\History\Models\Log::pushProcessor(new UnixGroupMemberships);
		}
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	protected function registerConsoleCommands(): void
	{
		$this->commands([
			EmailAuthorizedCommand::class,
			EmailRemovedCommand::class,
			SyncMembershipCommand::class,
			GroupAddMemberCommand::class,
			GroupRemoveMemberCommand::class,
			UnixGroupAddMemberCommand::class,
			UnixGroupRemoveMemberCommand::class,
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
		}, config('view.paths', [])), [$sourcePath]), $this->name);

		/*View::composer(
			'users::site.profile', ProfileComposer::class
		);*/
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
