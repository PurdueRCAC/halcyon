<?php

namespace App\Modules\Widgets\Console;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'widget:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setting up widgets folders for first use.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->generateWidgetsFolder();

		$this->generateAssetsFolder();
	}

	/**
	 * Generate the modules folder.
	 *
	 * @return void
	 */
	public function generateWidgetsFolder()
	{
		$this->generateDirectory(
			app_path('Widgets'),
			'Widgets directory created successfully',
			'Widgets directory already exist'
		);
	}

	/**
	 * Generate the assets folder.
	 *
	 * @return void
	 */
	public function generateAssetsFolder()
	{
		$this->generateDirectory(
			public_path('widgets'),
			'Assets directory created successfully',
			'Assets directory already exist'
		);
	}

	/**
	 * Generate the specified directory by given $dir.
	 *
	 * @param $dir
	 * @param $success
	 * @param $error
	 * @return void
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
