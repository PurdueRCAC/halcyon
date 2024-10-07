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
	protected $signature = 'widget:publish
						{widget?* : The name of the widget that will be used.}
						{--t|--tidy : Clean up old files}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Publish a widget\'s assets to the application';

	/**
	 * Execute the console command.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function publishAll()
	{
		$processed = array();
		foreach ($this->laravel['widget']->find() as $widget)
		{
			if (in_array($widget->widget, $processed))
			{
				continue;
			}
			$processed[] = $widget->widget;

			$this->publish(new Widget($widget));
		}
	}

	/**
	 * Publish assets from the specified theme.
	 *
	 * @param mixed $name string|Widget
	 * @return void
	 */
	public function publish($name)
	{
		if ($name instanceof Widget)
		{
			$widgets = array($name);
		}
		else
		{
			$widgets = $this->laravel['widget']->find($name);

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
				continue;
			}

			if (!$this->getFilesystem()->isDirectory($destinationPath))
			{
				$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
			}
			elseif ($this->option('tidy'))
			{
				$this->getFilesystem()->cleanDirectory($destinationPath);
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
}
