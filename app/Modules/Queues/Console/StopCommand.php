<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\Queue;

/**
 * Stop scheduling for queues
 */
class StopCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:stop {--r|resource= : Resource alias} {--queue= : Specific queue. Accepts ID or name. If name is used, must also supply a subresource.} {--subresource= : Subresource to find the queue in} {--debug : Output actions that will be taken}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Stop scheduling on one or more queues.';

	/**
	 * Execute the console command.
	 * 
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$q = $this->option('queue');
		$s = $this->option('subresource');

		if ($q)
		{
			if (is_numeric($q))
			{
				$queue = Queue::find($q);
			}
			else
			{
				if (!$s)
				{
					$this->error('A subresource must be specified when using a queue name');
					return;
				}

				if (is_numeric($s))
				{
					$subresource = Subresource::find($s);
				}
				else
				{
					$subresource = Subresource::query()
						->where('name', '=', $name)
						->limit(1)
						->get()
						->first();
				}

				if (!$subresource)
				{
					$this->error('Could not find specified subresource ' . $s);
					return;
				}

				$queue = Queue::query()
					->where('subresourceid', '=', $subresourceid)
					->where('name', '=', $name)
					->limit(1)
					->get()
					->first();
			}

			if (!$queue)
			{
				$this->error('Could not find specified queue ' . $q);
			}
			else
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->info('Stopping queue ' . $queue->id . ' (' . $queue->name . ')');
				}

				if (!$debug)
				{
					$queue->stop();
				}
			}
		}

		$r = $this->option('resource');

		if ($r)
		{
			if (is_numeric($r))
			{
				$resource = Asset::find($r);
			}
			else
			{
				$resource = Asset::findByName($r);
			}

			if (!$resource)
			{
				$this->error('Could not find specified resource ' . $r);
				return;
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->comment('Finding all subresources for resource ' . $resource->name . ' ...');
			}

			if ($resource)
			{
				foreach ($resource->subresources as $subresource)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->info('Stopping all queues for subresource ' . $subresource->name);

						if ($debug)
						{
							continue;
						}
					}

					$subresource->stopQueues();
				}

				$scheduler = Scheduler::query()
					->where(function($where) use ($resource)
					{
						$where->where('hostname', '=', $resource->rolename . '-adm.rcac.purdue.edu')
							->orWhere('hostname', '=', $resource->rolename . '.adm.rcac.purdue.edu')
							->orWhere('hostname', '=', 'adm.' . $resource->rolename . '.rcac.purdue.edu');
					})
					->get()
					->first();

				if ($scheduler && config('module.queues.stop_all_cmd'))
				{
					$command = config('module.queues.stop_all_cmd');
					$command = str_replace('$HOST', $scheduler->hostname, $command);

					if ($debug || $this->output->isVerbose())
					{
						$this->comment('Executing command "' . $command . '" ...');

						if ($debug)
						{
							return;
						}
					}

					$retval = true; // Assume success.

					$command = escapeshellcmd($command);

					exec($command, $results, $status);

					if (is_array($results))
					{
						$results = implode('', $results);
					}
					$results = trim($results);

					// Check exec status
					if ($status != 0)
					{
						// Uh-oh. Something went wrong...
						$this->error($results);
					}
					else
					{
						$this->info($results);
					}
				}
			}
		}
	}
}
