<?php

namespace App\Modules\Resources\Console;

//use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Resources\Entities\Subresource;
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

		$stopped = Subresource::query()
			->where('notice', '=', 2)
			->get();

		if (count($stopped))
		{
			$message = new Scheduling('stopped', array(), $stopped);

			if ($debug)
			{
				echo $message->render();
			}
			else
			{
				Mail::to($email)->send($message);

				$this->info("Emailed stopped scheduling to {$email}.");
			}

			foreach ($stopped as $subresource)
			{
				$subresource->update(['notice' => 3]);
			}
		}
		else
		{
			$this->info("No stopped queues found.");
		}

		$started = Subresource::query()
			->where('notice', '=', 1)
			->get();

		$stopped = Subresource::query()
			->where('notice', '=', 3)
			->get();

		if (count($started))
		{
			$message = new Scheduling('started', $started, $stopped);

			if ($debug)
			{
				echo $message->render();
			}
			else
			{
				Mail::to($email)->send($message);

				$this->info("Emailed started scheduling to {$email}.");
			}

			foreach ($started as $subresource)
			{
				$subresource->update(['notice' => 0]);
			}
		}
		else
		{
			$this->info("No newly started queues found.");
		}
	}
}
