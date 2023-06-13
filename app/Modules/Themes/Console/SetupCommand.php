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
	public function handle(): int
	{
		if (!$this->generateThemesFolder())
		{
			return Command::FAILURE;
		}

		if (!$this->generateAssetsFolder())
		{
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * Generate the themes folder.
	 *
	 * @return  bool
	 */
	public function generateThemesFolder(): bool
	{
		return $this->generateDirectory(
			app_path('Themes'),
			'Themes directory created successfully',
			'Themes directory already exist'
		);
	}

	/**
	 * Generate the assets folder.
	 *
	 * @return  bool
	 */
	public function generateAssetsFolder(): bool
	{
		return $this->generateDirectory(
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
	 * @return bool
	 */
	protected function generateDirectory(string $dir, string $success, string $error): bool
	{
		if (!$this->laravel['files']->isDirectory($dir))
		{
			if ($this->laravel['files']->makeDirectory($dir, 0755, true, true))
			{
				$this->info($success);

				return true;
			}

			$error = 'Failed to create directory';
		}

		$this->error($error);

		return false;
	}
}
