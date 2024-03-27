<?php

namespace App\Modules\Resources\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Mail\Scheduling;
use App\Modules\History\Models\Log;

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
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;
		$email = config('module.resources.admin_email');

		$a = (new Asset)->getTable();
		$s = (new Subresource)->getTable();
		$c = (new Child)->getTable();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', Subresource::NOTICE_JUST_STOPPED)
			->get();

		if (count($stopped))
		{
			$message = new Scheduling('stopped', array(), $stopped);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug)
			{
				$this->info("Emailed stopped scheduling to {$email}.");
			}
			else
			{
				Mail::to($email)->send($message);

				if ($this->output->isVerbose())
				{
					$this->info("Emailed stopped scheduling to {$email}.");
				}

				foreach ($stopped as $subresource)
				{
					$subresource->update(['notice' => Subresource::NOTICE_STILL_STOPPED]);
				}
			}
		}
		elseif ($debug || $this->output->isVerbose())
		{
			$this->info('No stopped queues found.');
		}

		$started = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', Subresource::NOTICE_JUST_STARTED)
			->get();

		$stopped = Subresource::query()
			->select($s . '.*')
			->join($c, $c . '.subresourceid', $s . '.id')
			->join($a, $a . '.id', $c . '.resourceid')
			->whereNull($a . '.datetimeremoved')
			->where($s . '.notice', '=', Subresource::NOTICE_STILL_STOPPED)
			->get();

		if (count($started))
		{
			$message = new Scheduling('started', $started, $stopped);
			$message->headers()->text([
				'X-Command' => 'resources:emailscheduling',
			]);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info("Emailed started scheduling to {$email}.");

				if ($debug)
				{
					return;
				}
			}

			Mail::to($email)->send($message);

			foreach ($started as $subresource)
			{
				$subresource->update(['notice' => Subresource::NO_NOTICE]);
			}
		}
		elseif ($debug || $this->output->isVerbose())
		{
			$this->info('No newly started queues found.');
		}
	}
}
