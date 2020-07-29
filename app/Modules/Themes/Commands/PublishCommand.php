<?php

namespace App\Modules\Themes\Commands;

use Illuminate\Console\Command;
use App\Modules\Themes\Entities\Theme;
use Nwidart\Modules\Publishing\AssetPublisher;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish a theme\'s assets to the application';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if ($name = $this->argument('theme'))
		{
			$this->publish($name);

			return;
		}

		$this->publishAll();
	}

	/**
	 * Publish assets from all modules.
	 */
	public function publishAll()
	{
		foreach ($this->laravel['themes']->allEnabled() as $theme)
		{
			$this->publish($theme);
		}
	}

	/**
	 * Publish assets from the specified theme.
	 *
	 * @param string $name
	 */
	public function publish($name)
	{
		if ($name instanceof Theme)
		{
			$theme = $name;
		}
		else
		{
			$theme = $this->laravel['themes']->findOrFail($name);
		}

		with(new AssetPublisher($theme))
			->setRepository($this->laravel['themes'])
			->setConsole($this)
			->publish();

		$this->line("<info>Published</info>: {$theme->getStudlyName()}");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['theme', InputArgument::OPTIONAL, 'The name of the theme to be used.'],
		];
	}
}
