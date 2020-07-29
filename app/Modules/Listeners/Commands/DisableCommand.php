<?php

namespace App\Modules\Listeners\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DisableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'listener:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disable the specified listener.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$listener = $this->laravel['listeners']->findOrFail($this->argument('listener'));

		if ($listener->enabled())
		{
			$listener->disable();

			$this->info("Listener [{$listener}] disabled successful.");
		}
		else
		{
			$this->comment("Listener [{$listener}] has already disabled.");
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
