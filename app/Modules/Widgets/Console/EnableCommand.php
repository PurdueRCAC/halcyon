<?php

namespace App\Modules\Widgets\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class EnableCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'widget:enable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Enable the specified widget.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$widget = $this->laravel['widget']->find($this->argument('widget'))->first();

		if ($widget->isDisabled())
		{
			$widget->update(['published' => 1]);

			$this->info("Widget `{$widget}` successfully enabled.");
		}
		else
		{
			$this->comment("Widget `{$widget}` is already enabled.");
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
