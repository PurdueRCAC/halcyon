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
		$enabled = $this->option('enabled') ? true : false;
		$disabled = $this->option('disabled') ? true : false;

		$listeners = $this->laravel['listener']->all()->sortBy('folder');

		if ($enabled)
		{
			$listeners = $listeners->filter(function($value, $key)
			{
				if ($value->enabled)
				{
					return true;
				}

				return false;
			});
		}
		elseif ($disabled)
		{
			$listeners = $listeners->filter(function($value, $key)
			{
				if (!$value->enabled)
				{
					return true;
				}

				return false;
			});
		}

		if (!count($listeners))
		{
			$this->error("No listeners found.");
			return;
		}

		foreach ($listeners as $listener)
		{
			if ($listener->enabled)
			{
				$this->info('[enabled]  ' . $listener->name . ' (folder: ' . $listener->folder . ', element: ' . $listener->element . ')');
			}
			else
			{
				$this->comment('[disabled] ' . $listener->name . ' (folder: ' . $listener->folder . ', element: ' . $listener->element . ')');
			}
		}
	}
}
