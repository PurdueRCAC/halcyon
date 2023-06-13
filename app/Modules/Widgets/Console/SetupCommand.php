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
	 * @return int
	 */
	public function handle(): int
	{
		if (!$this->generateWidgetsFolder())
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
	 * Generate the modules folder.
	 *
	 * @return bool
	 */
	public function generateWidgetsFolder(): bool
	{
		return $this->generateDirectory(
			app_path('Widgets'),
			'Widgets directory created successfully',
			'Widgets directory already exist'
		);
	}

	/**
	 * Generate the assets folder.
	 *
	 * @return bool
	 */
	public function generateAssetsFolder(): bool
	{
		return $this->generateDirectory(
			public_path('widgets'),
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

		$this->comment($error);

		return false;
	}
}
