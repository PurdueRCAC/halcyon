<?php

namespace App\Modules\History\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\History\Listeners\LogSentMessage;
use App\Modules\History\Listeners\LogCommand;
use App\Modules\History\Models\Log;
use App\Modules\History\LogProcessors\TargetUsers;
use App\Modules\History\LogProcessors\Emails;

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
	 * @var array
	 */
	protected $listen = [
		/*'Illuminate\Mail\Events\MessageSending' => [
			'App\Listeners\LogSendingMessage',
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

		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

		//$this->app['events']->listen('Illuminate\Mail\Events\MessageSent', LogSentMessage::class);
		$this->app['events']->listen('Illuminate\Console\Events\CommandFinished', LogCommand::class);

		Log::pushProcessor(new TargetUsers);
		Log::pushProcessor(new Emails);
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
