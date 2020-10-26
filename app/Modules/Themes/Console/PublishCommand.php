<?php

namespace App\Modules\Themes\Console;

use Illuminate\Console\Command;
use App\Modules\Themes\Entities\Theme;
//use App\Modules\Themes\Publishing\AssetPublisher;
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

		/*with(new AssetPublisher($theme))
			->setRepository($this->laravel['themes'])
			->setConsole($this)
			->publish();*/

		$sourcePath = $theme->getPath() . '/assets'; //config('module.themes.paths.themes', app_path('Themes'));
		$destinationPath = $this->laravel['themes']->getAssetPath($theme->getLowerName());

		if (!$this->getFilesystem()->isDirectory($sourcePath))
		{
			$this->error('Themes source path not found: ' . $sourcePath);
			return;
		}

		if (!$this->getFilesystem()->isDirectory($destinationPath))
		{
			$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
		}

		if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath))
		{
			$this->line("<info>Published</info>: {$theme->getStudlyName()}");
		}
		else
		{
			$this->error('Failed to copy assets for ' . $theme->getStudlyName());
		}
	}

	/**
	 * @return \Illuminate\Filesystem\Filesystem
	 */
	protected function getFilesystem()
	{
		return $this->laravel['files'];
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
