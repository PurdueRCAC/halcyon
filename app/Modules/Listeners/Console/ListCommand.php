<?php

namespace App\Modules\Listeners\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class ListCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'listener:list {--enabled : Filter list to just enabled listeners} {--disabled : Filter list to just disabled listeners}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'List installed listeners';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$enabled  = $this->option('enabled')  ? true : false;
		$disabled = $this->option('disabled') ? true : false;

		$listeners = $this->laravel['listener']->all()->sortBy('folder');

		if ($enabled)
		{
			$listeners = $listeners->filter(function($value, $key)
			{
				return ($value->enabled ? true : false);
			});
		}
		elseif ($disabled)
		{
			$listeners = $listeners->filter(function($value, $key)
			{
				return (!$value->enabled ? true : false);
			});
		}

		if (!count($listeners))
		{
			$this->error('No listeners found.');
			return;
		}

		$rows = array();

		foreach ($listeners as $listener)
		{
			$rows[] = [$listener->name, ($listener->enabled ? 'Enabled' : 'Disabled'), $listener->folder, $listener->element, $listener->path];
		}

		$this->table(['Name', 'Status', 'Folder', 'Element', 'Path'], $rows);
	}
}
