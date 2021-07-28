<?php

namespace App\Modules\Listeners\Console;

use Illuminate\Console\Command;
use App\Modules\Listeners\Models\Listener;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'listener:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish a listener\'s assets to the application';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if ($folder = $this->argument('folder'))
		{
			$this->publish($folder, $this->argument('element'));

			return;
		}

		$this->publishAll();
	}

	/**
	 * Publish assets from all listeners.
	 */
	public function publishAll()
	{
		foreach ($this->laravel['listener']->all() as $listener)
		{
			$this->publish($listener);
		}
	}

	/**
	 * Publish assets from the specified listener.
	 *
	 * @param string $folder
	 * @param string $name
	 */
	public function publish($folder, $name = null)
	{
		if ($folder instanceof Listener)
		{
			$listener = $folder;
		}
		else
		{
			$listener = $this->laravel['listener']->byType($folder, $name)->first();
		}

		if (!$listener || !$listener->path)
		{
			$this->error('Failed to find listener ' . ($listener ? $listener->name : $folder . ' ' . $name));
			return;
		}

		/*with(new AssetPublisher($listener))
			->setRepository($this->laravel['listener'])
			->setConsole($this)
			->publish();*/

		$sourcePath = $listener->getAssetPath();
		$destinationPath = $listener->getPublicAssetPath();

		if (!$this->getFilesystem()->isDirectory($sourcePath))
		{
			$this->line('No assets found for: ' . $listener->name);
			return;
		}

		if (!$this->getFilesystem()->isDirectory($destinationPath))
		{
			$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
		}

		if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath))
		{
			$this->line("<info>Published</info>: {$listener->name}");
		}
		else
		{
			$this->error('Failed to copy assets for ' . $listener->name);
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
			['folder', InputArgument::OPTIONAL, 'The type/folder of the listener.'],
			['element', InputArgument::OPTIONAL, 'The element name of the listener.'],
		];
	}
}
