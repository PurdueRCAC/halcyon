<?php

namespace App\Modules\Themes\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
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
	 *
	 * @return  void
	 */
	public function handle(): void
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
	 *
	 * @return  void
	 */
	public function publishAll(): void
	{
		foreach ($this->laravel['themes']->allEnabled() as $theme)
		{
			$this->publish($theme);
		}
	}

	/**
	 * Publish assets from the specified theme.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function publish($name): void
	{
		if ($name instanceof Theme)
		{
			$theme = $name;
		}
		else
		{
			$theme = $this->laravel['themes']->find($name);
		}

		if (!$theme)
		{
			$this->error(trans('themes::themes.error.failed to find theme', ['name' => $name]));
			return;
		}

		/*with(new AssetPublisher($theme))
			->setRepository($this->laravel['themes'])
			->setConsole($this)
			->publish();*/

		$sourcePath = $theme->getPath() . '/assets';
		$destinationPath = $this->laravel['themes']->getAssetPath($theme->getLowerName());

		if (!$this->getFilesystem()->isDirectory($sourcePath))
		{
			$this->error(trans('themes::themes.error.source path not found', ['path' => $sourcePath]));
			return;
		}

		if (!$this->getFilesystem()->isDirectory($destinationPath))
		{
			$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
		}

		if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath))
		{
			$this->components->task($theme->getStudlyName(), fn() => true);
		}
		else
		{
			$this->error(trans('themes::themes.error.failed to publish assets', ['name' => $theme->getStudlyName()]));
		}
	}

	/**
	 * @return Filesystem
	 */
	protected function getFilesystem(): Filesystem
	{
		return $this->laravel['files'];
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array<int,array>
	 */
	protected function getArguments(): array
	{
		return [
			['theme', InputArgument::OPTIONAL, 'The name of the theme to be used.'],
		];
	}
}
