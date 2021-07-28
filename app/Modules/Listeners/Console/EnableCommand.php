<?php

namespace App\Modules\Listeners\Console;

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
	protected $description = 'Enable the specified listener';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$listener = $this->laravel['listener']->byType(
			$this->argument('folder'),
			$this->argument('element')
		)->first();

		if (!$listener)
		{
			$this->error("Listener not found.");
			return;
		}

		if (!$listener->enabled)
		{
			$listener->update(['enabled' => 1]);

			$this->info("Listener successfully enabled.");
		}
		else
		{
			$this->comment("Listener is already enabled.");
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
			['folder', InputArgument::OPTIONAL, 'The type/folder of the listener.'],
			['element', InputArgument::OPTIONAL, 'The element name of the listener.'],
		];
	}
}
