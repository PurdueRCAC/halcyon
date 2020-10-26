<?php

namespace App\Modules\Themes\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class EnableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'theme:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enable the specified theme.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$theme = $this->laravel['themes']->findOrFail($this->argument('theme'));

		if ($theme->disabled())
		{
			$theme->enable();

			$this->info("Theme `{$theme}` successfully enabled.");
		}
		else
		{
			$this->comment("Theme `{$theme}` is already enabled.");
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['theme', InputArgument::REQUIRED, 'Theme name.'],
		];
	}
}
