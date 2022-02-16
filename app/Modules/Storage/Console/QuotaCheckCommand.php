<?php

namespace App\Modules\Storage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Storage\Models\Usage;

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
			->withTrashed()
			->select($d . '.*', $r . '.getquotatypeid')
			->join($r, $r . '.id', $d . '.storageresourceid')
			->where($r . '.parentresourceid', '=', 64)
			->where(function($where) use ($r, $d)
				{
					$where->where($d . '.bytes', '<>', 0)
						->orWhere($r . '.defaultquotaspace', '<>', 0);
				})
			->whereNull($d . '.datetimeremoved')
			->whereNull($r . '.datetimeremoved')
			->get();

		$u = (new Usage)->getTable();

		$usages = DB::select(DB::raw("SELECT resourceid,
				storagedirid,
				quota AS lastquota,
				space AS lastspace,
				lastcheck,
				lastinterval,
				LEAST(1, (SUM(tb1.var) / SUM(tb1.max)) * GREATEST(1, 5 * POW((space / quota) , 28))) AS normalvariability FROM
					(SELECT $u.id,
						$d.resourceid,
						$u.storagedirid,
						$u.quota,
						$u.space,
						$u.lastinterval,
						MAX($u.datetimerecorded) AS lastcheck,
						LEFT($u.datetimerecorded, 10) AS day,
						(((COUNT(DISTINCT $u.space)-1) / COUNT($u.space)) * EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT($u.datetimerecorded, 10)))/86400)+1)*0.25)) as var,
							(EXP(-(((UNIX_TIMESTAMP(LEFT(NOW(), 10)) - UNIX_TIMESTAMP(LEFT($u.datetimerecorded, 10)))/86400)+1)*0.25)) AS max
					FROM $u,
						$d
					WHERE $u.datetimerecorded >= DATE_SUB(NOW(), INTERVAL 10 DAY) AND
						$u.storagedirid <> 0
						AND ($u.quota <> 0 OR $u.space <> 0)
						AND $d.id = $u.storagedirid
					GROUP BY $u.storagedirid,
						day, $u.id
					ORDER BY $u.storagedirid,
						$u.datetimerecorded DESC) AS tb1
			GROUP BY tb1.storagedirid, tb1.quota, tb1.space, tb1.lastcheck, tb1.lastinterval"));

		$usag = array();
		foreach ($usages as $u)
		{
			$usag[$u->storagedirid] = $u;
		}

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
			if (!$usage || !$usage->id || !isset($usag[$dir->id]))
			{
				if ($dir->getquotatypeid)
				{
					// Check for pending requests
					$message = $dir->messages()
						->where('messagequeuetypeid', '=', $dir->getquotatypeid)
						->whereNull('datetimecompleted')
						->count();

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
			//$var = $usage->normalvariability;
			$var = $usag[$dir->id]->normalvariability;
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
					->whereNull('datetimecompleted')
					->count();

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
