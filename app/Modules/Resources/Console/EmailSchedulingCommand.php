<?php

namespace App\Modules\Resources\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Mail\Scheduling;

class EmailSchedulingCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'resources:emailscheduling {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email started/stopped status of scheduling on subresources.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;
		$email = 'rcac-alerts@lists.purdue.edu';

		$a = (new Asset)->getTable();
		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->withTrashed()
			->whereIsActive()
			->where(function($where) use ($a)
			{
				$where->whereNull($a . '.datetimeremoved')
					->orWhere($a . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where($s . '.notice', '=', 2)
			->get();

		if (count($stopped))
		{
			$message = new Scheduling('stopped', array(), $stopped);

			if ($debug)
			{
				echo $message->render();
				$this->info("Emailed stopped scheduling to {$email}.");
			}
			else
			{
				Mail::to($email)->send($message);

				//$this->info("Emailed stopped scheduling to {$email}.");

				foreach ($stopped as $subresource)
				{
					$subresource->update(['notice' => 3]);
				}
			}
		}
		elseif ($debug)
		{
			
			$this->info('No stopped queues found.');
		}

		$started = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->withTrashed()
			->whereIsActive()
			->where(function($where) use ($a)
			{
				$where->whereNull($a . '.datetimeremoved')
					->orWhere($a . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where($s . '.notice', '=', 1)
			->get();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->withTrashed()
			->whereIsActive()
			->where(function($where) use ($a)
			{
				$where->whereNull($a . '.datetimeremoved')
					->orWhere($a . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where($s . '.notice', '=', 3)
			->get();

		if (count($started))
		{
			$message = new Scheduling('started', $started, $stopped);

			if ($debug)
			{
				echo $message->render();
				$this->info("Emailed started scheduling to {$email}.");
			}
			else
			{
				Mail::to($email)->send($message);

				//$this->info("Emailed started scheduling to {$email}.");

				foreach ($started as $subresource)
				{
					$subresource->update(['notice' => 0]);
				}
			}
		}
		elseif ($debug)
		{
			$this->info('No newly started queues found.');
		}
	}
}
