<?php

namespace App\Modules\Themes\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DisableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disable the specified theme.';

	/**
	 * Execute the console command.
	 *
	 * @return  void
	 */
	public function handle(): void
	{
		$module = $this->laravel['modules']->findOrFail($this->argument('module'));

		if ($module->enabled())
		{
			$module->disable();

			$this->info("Theme [{$theme}] disabled successful.");
		}
		else
		{
			$this->comment("Theme [{$theme}] has already disabled.");
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array<int,array>
	 */
	protected function getArguments()
	{
		return [
			['theme', InputArgument::REQUIRED, 'Theme name.'],
		];
	}
}
