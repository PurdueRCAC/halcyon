<?php

namespace App\Modules\Widgets\Console;

use Illuminate\Console\Command;
use App\Modules\Widgets\Entities\Widget;
use Symfony\Component\Console\Input\InputArgument;

class PublishCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'widget:publish';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish a widget\'s assets to the application';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if ($name = $this->argument('widget'))
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
		foreach ($this->laravel['widget']->all() as $widget)
		{
			$this->publish(new Widget($widget));
		}
	}

	/**
	 * Publish assets from the specified theme.
	 *
	 * @param mixed $name string|Widget
	 */
	public function publish($name)
	{
		if ($name instanceof Widget)
		{
			$widgets = array($name);
		}
		else
		{
			$widgets = $this->laravel['widget']->all()
				->filter(function($value, $key) use ($name)
				{
					if ($value->name == $name)
					{
						return true;
					}

					return false;
				});
			foreach ($widgets as $widget)
			{
				$widget = new Widget($widget);
			}
		}

		foreach ($widgets as $widget)
		{
			$sourcePath = $widget->getPath() . '/assets';
			$destinationPath = $this->laravel['widget']->getAssetPath($widget->getLowerName());

			if (!$this->getFilesystem()->isDirectory($sourcePath))
			{
				//$this->error('[skipping] Widgets source path not found: ' . $sourcePath);
				//return;
				continue;
			}

			if (!$this->getFilesystem()->isDirectory($destinationPath))
			{
				$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
			}

			if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath))
			{
				$this->line("<info>Published</info>: {$widget->getStudlyName()}");
			}
			else
			{
				$this->error('Failed to copy assets for ' . $widget->getStudlyName());
			}
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
			['widget', InputArgument::OPTIONAL, 'The name of the widget to be used.'],
		];
	}
}
