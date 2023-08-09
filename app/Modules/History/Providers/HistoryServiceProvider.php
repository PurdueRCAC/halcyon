<?php

namespace App\Modules\History\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\History\Listeners\LogSendingMessage;
use App\Modules\History\Listeners\LogCommand;
use App\Modules\History\Models\Log;
use App\Modules\History\LogProcessors\TargetUsers;
use App\Modules\History\LogProcessors\Emails;
use App\Modules\History\Console\PurgeLogCommand;
use App\Modules\History\Console\PurgeHistoryCommand;

class HistoryServiceProvider extends ServiceProvider
{
	/**
	 * Module name
	 *
	 * @var string
	 */
	public $name = 'history';

	/**
	 * The event listener mappings for the application.
	 *
	 * @var array<string,array>
	 */
	protected $listen = [
		/*'Illuminate\Mail\Events\MessageSending' => [
			LogSentMessage::class,
		],
		'Illuminate\Mail\Events\MessageSent' => [
			LogSentMessage::class,
		],*/
	];

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerTranslations();
		$this->registerViews();
		$this->registerConsoleCommands();

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		// Log sent emails
		$this->app['events']->listen('Illuminate\Mail\Events\MessageSending', LogSendingMessage::class);

		// Log artisan commands
		$this->app['events']->listen('Illuminate\Console\Events\CommandFinished', LogCommand::class);

		Log::pushProcessor(new TargetUsers);
		Log::pushProcessor(new Emails);
	}

	/**
	 * Register console commands.
	 *
	 * @return void
	 */
	protected function registerConsoleCommands()
	{
		$this->commands([
			PurgeLogCommand::class,
			PurgeHistoryCommand::class,
		]);
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
