<?php

namespace App\Modules\Themes\Console;

use Illuminate\Console\Command;

class SetupCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:setup';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Setting up themes folders for first use.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$this->generateThemesFolder();

		$this->generateAssetsFolder();
	}

	/**
	 * Generate the themes folder.
	 *
	 * @return  void
	 */
	public function generateThemesFolder()
	{
		$this->generateDirectory(
			app_path('Themes'),
			'Themes directory created successfully',
			'Themes directory already exist'
		);
	}

	/**
	 * Generate the assets folder.
	 *
	 * @return  void
	 */
	public function generateAssetsFolder()
	{
		$this->generateDirectory(
			public_path('themes'),
			'Assets directory created successfully',
			'Assets directory already exist'
		);
	}

	/**
	 * Generate the specified directory by given $dir.
	 *
	 * @param  string $dir
	 * @param  string $success
	 * @param  string $error
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

		$this->error($error);
	}
}
