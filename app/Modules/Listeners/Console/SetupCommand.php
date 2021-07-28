<?php

namespace App\Modules\Listeners\Console;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'listener:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setting up listeners folders for first use';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$this->generateListenersFolder();

		$this->generateAssetsFolder();
	}

	/**
	 * Generate the themes folder.
	 */
	public function generateListenersFolder()
	{
		$this->generateDirectory(
			app_path('Listeners'),
			'Listeners directory created successfully',
			'Listeners directory already exist'
		);
	}

	/**
	 * Generate the assets folder.
	 */
	public function generateAssetsFolder()
	{
		$this->generateDirectory(
			public_path('listeners'),
			'Assets directory created successfully',
			'Assets directory already exist'
		);
	}

	/**
	 * Generate the specified directory by given $dir.
	 *
	 * @param string $dir
	 * @param string $success
	 * @param string $error
	 */
	protected function generateDirectory($dir, $success, $error)
	{
		if (!$this->laravel['files']->isDirectory($dir))
		{
			$this->laravel['files']->makeDirectory($dir, 0755, true, true);

			$this->info($success);

			return;
		}

		$this->comment($error);
	}
}
