<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use App\Modules\Queues\Events\Schedule;
use App\Modules\Resources\Models\Asset;

class ScheduleCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'queues:schedule {--r|resource= : Resource alias} {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Set default QoS for appropriate queues.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$r = $this->option('resource');
		$r = ltrim($r, '=');

		if (!$r)
		{
			$this->error('No resource provided');
			return;
		}

		$resource = Asset::findByName($r);

		if (!$resource)
		{
			$this->error('Invalid resource provided ' . $r);
			return;
		}

		/*if ($this->output->isVerbose())
		{
			$this->line('Triggering Schedule event');
		}*/

		event($e = new Schedule($resource, $this, $this->output->isVerbose()));
	}
}
