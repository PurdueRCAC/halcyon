<?php

namespace App\Modules\Storage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;

class QuotaCheckCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'storage:quotacheck {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check storage quota.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$d = (new Directory)->getTable();
		$r = (new StorageResource)->getTable();

		$dirs = Directory::query()
			->select($d . '.*', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->where($r . '.parentresourceid', '=', 64)
			->where(function($where) use ($r, $d)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
			->where(function($where) use ($d)
				{
					$where->whereNull($d . '.datetimeremoved')
						->orWhere($d . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
			->where(function($where) use ($r)
				{
					$where->whereNull($r . '.datetimeremoved')
						->orWhere($r . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
			->get();

		if (!count($dirs))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No storage directories found');
			}
			return;
		}

		$param = array(
			'target'  => 0.2,
			'min'     => 0.25,
			'max'     => 12,
			'start'   => 2,
			'rampup'  => 2,
			'backoff' => 1.5,
		);
		$started = array();
		$skipped = array();
		$now = date("U");

		foreach ($dirs as $dir)
		{
			$type = $dir->getquotatypeid;

			if (!isset($started[$type]))
			{
				$started[$type] = 0;
			}

			if (!isset($skipped[$type]))
			{
				$skipped[$type] = 0;
			}

			$usage = $dir->usage()
				->orderBy('datetimerecorded', 'desc')
				->limit(1)
				->get()
				->first();

			// Force refresh?
			if (!$usage)
			{
				if ($dir->getquotatypeid)
				{
					// Check for pending requests
					$message = $dir->messages()
						->where('messagequeuetypeid', '=', $dir->getquotatypeid)
						->where(function($where)
						{
							$where->whereNull('datetimecompleted')
								->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
						})
						->get()
						->first();

					if (!$message)
					{
						$dir->addMessageToQueue($dir->getquotatypeid, auth()->user() ? auth()->user()->id : 0);

						$started[$type]++;
					}
					else
					{
						$skipped[$type]++;
					}
				}

				continue;
			}

			// Grab paramaters
			$target_var = $param['target'];

			// Grab starting values
			$var = $usage->normalvariability;
			$last_interval = $usage->lastinterval;
			$last_check = strtotime($usage->datetimerecorded);

			$this_interval = $param['start'] * 60 * 60;

			if (!$last_interval)
			{
				// Only have one data point, let's use the starting interval
				$this_interval = $param['start'] * 60 * 60;
			}
			else
			{
				if ($var > $target_var)
				{
					// Ramp up
					$this_interval = round(max($param['min'] * 60 * 60, $last_interval / $param['rampup']));
				}
				else if ($var < $target_var)
				{
					// Back off
					$this_interval = round(min($param['max'] * 60 * 60, $last_interval * $param['backoff']));
				}
				else
				{
					// Just right!
					$this_interval = $last_interval;
				}
			}

			// OK, check if it's time to get this check going
			if ($now - $last_check >= $this_interval)
			{
				$message = $dir->messages()
					->where('messagequeuetypeid', '=', $dir->getquotatypeid)
					->where(function($where)
					{
						$where->whereNull('datetimecompleted')
							->orWhere('datetimecompleted', '=', '0000-00-00 00:00:00');
					})
					->get()
					->first();

				if (!$message)
				{
					$dir->addMessageToQueue($dir->getquotatypeid, auth()->user() ? auth()->user()->id : 0);

					$started[$type]++;
				}
				else
				{
					$skipped[$type]++;
				}
			}
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Started:');
			foreach ($started as $key => $value)
			{
				$this->line("\tmessagequeuetypeid: " . $key . ", count: " . $value);
			}

			$this->info('Skipped:');
			foreach ($skipped as $key => $value)
			{
				$this->line("\tmessagequeuetypeid: " . $key . ", count: " . $value);
			}
		}
	}
}
