<?php

namespace App\Modules\Listeners\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class EnableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'listener:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enable the specified listener.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$listener = $this->laravel['listeners']->findOrFail($this->argument('listener'));

		if ($listener->disabled())
		{
			$listener->enable();

			$this->info("Listener [{$listener}] enabled successful.");
		}
		else
		{
			$this->comment("Listener [{$listener}] has already enabled.");
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
			['listener', InputArgument::REQUIRED, 'Listener name.'],
		];
	}
}
