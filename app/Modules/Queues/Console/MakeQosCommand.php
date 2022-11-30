<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Queues\Models\Qos;
//use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\QueueSizeCreated;
use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Resources\Models\Asset;

class MakeQosCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'queues:makeqos {--r|resource= : Resource alias} {--debug}';

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
			$this->error('Invalid resource provided' . $r);
			return;
		}

		foreach ($resource->subresources as $subresource)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('Looking up schedulers for subresource "' . $subresource->name . '"');
			}

			$schedulers = Scheduler::query()
				->where('queuesubresourceid', '=', $subresource->id)
				->get();

			if (!count($schedulers))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('    No schedulers found. Skipping...');
				}
				continue;
			}

			foreach ($schedulers as $scheduler)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('Looking up queues for scheduler "' . $scheduler->hostname . '"');
				}

				foreach ($scheduler->queues as $queue)
				{
					if ($queue->groupid <= 0)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->info('Skipping system queue "' . $queue->name . '"');
						}
						continue;
					}

					$qos = Qos::query()
						->where('name', '=', $queue->defaultQosName)
						->where('scheduler_id', '=', $queue->schedulerid)
						->first();

					if ($qos)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->info('Default QoS already exists for queue "' . $queue->name . '"');
						}

						continue;
					}

					if (!$queue->serviceunits && !$queue->totalcores)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->info('Queue "' . $queue->name . '" has no allocations. Skipping...');
						}

						continue;
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->comment('Making default QoS for queue "' . $queue->name . '"');
						if ($debug)
						{
							continue;
						}
					}

					$purchase = $queue->sizes()->first();

					if ($purchase)
					{
						event(new QueueSizeCreated($purchase));
					}
					else
					{
						$loan = $queue->loans()->first();

						if ($loan)
						{
							event(new QueueLoanCreated($loan));
						}
					}
				}
			}
		}
	}
}
