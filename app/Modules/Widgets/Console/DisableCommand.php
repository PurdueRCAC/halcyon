<?php

namespace App\Modules\Widgets\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DisableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'widget:disable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Disable the specified widget.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$widget = $this->laravel['widget']->find($this->argument('widget'))->first();

		if ($widget->isDisabled())
		{
			$widget->update(['published' => 0]);

			$this->info("Widget [{$widget}] disabled successful.");
		}
		else
		{
			$this->comment("Widget [{$widget}] has already disabled.");
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
			['widget', InputArgument::REQUIRED, 'Widget name.'],
		];
	}
}
