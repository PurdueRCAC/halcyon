<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use App\Modules\Queues\Models\Queue;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Subresource;
use Carbon\Carbon;

class AuditCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'queues:audit {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Audit queue allocations.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();
		$a = (new Asset)->getTable();

		$subresources = Subresource::query()
			->select($s . '.id')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->get()
			->pluck('id')
			->toArray();

		$queues = Queue::query()
			->onlyTrashed()
			->where('groupid', '>', 0)
			->whereIn('subresourceid', $subresources)
			->orderBy('id', 'asc')
			->get();

		if (!count($queues))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No active queues found.');
			}
			return;
		}

		$now = Carbon::now()->toDateTimeString();

		foreach ($queues as $queue)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('Processing Queue #' . $queue->id . ' - ' . $queue->name . ' (' . $queue->subresource->name . ')');
			}

			foreach ($queue->sizes()->whereNull('datetimestop')->get() as $purchase)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('    Ending purchase #' . $purchase->id);
					if ($debug)
					{
						continue;
					}
				}
				$purchase->updateQuietly(['datetimestop' => $now]);
			}

			// Only stop counter entries.
			$sold = $queue->sold()
				->whereNull('datetimestop')
				->where('corecount', '<', 0)
				->get();
			foreach ($sold as $purchase)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('    Ending sale #' . $purchase->id);
					if ($debug)
					{
						continue;
					}
				}
				$purchase->updateQuietly(['datetimestop' => $now]);
			}

			foreach ($queue->loans()->whereNull('datetimestop')->get() as $loan)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('    Ending loan #' . $loan->id);
					if ($debug)
					{
						continue;
					}
				}
				$loan->updateQuietly(['datetimestop' => $now]);
			}

			foreach ($queue->loaned()->whereNull('datetimestop')->get() as $loan)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('    Ending loaned #' . $loan->id);
					if ($debug)
					{
						continue;
					}
				}
				$loan->updateQuietly(['datetimestop' => $now]);
			}

			foreach ($queue->users as $user)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('    Removing user entry #' . $user->id .', user ID #' . $user->userid);
					if ($debug)
					{
						continue;
					}
				}
				$user->deleteQuietly();
			}
		}
	}
}
