<?php

namespace App\Modules\History\Console;

use Illuminate\Console\Command;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

class PurgeLogCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'log:purge
							{app? : (optional) Limit to entries for a specific app}
							{--days=365 : Number of days to retain logs}
							{--level=1 : Level 1) Delete only logged out users and GET requests, Level 2) Delete only logged out users, Level 3) Delete all}
							{--debug : (optional) Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Purge log entries older than the specified days.';

	/**
	 * Execute the console command.
	 *
	 * @return  void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$app = $this->argument('app');

		$level = $this->option('level');
		$level = $level ?: 1;

		$maxAge = $this->option('days');
		$maxAge = $maxAge ?: config('module.history.max_log_age', 365);

		$cutOffDate = Carbon::now()
			->subDays($maxAge)
			->format('Y-m-d H:i:s');

		$query = Log::query()
			->where('datetime', '<', $cutOffDate);

		if ($app)
		{
			$query->where('app', '=', $app);
		}

		switch ($level)
		{
			case 3:
				// Delete everything
			break;

			case 2:
				// Delete everything from logged out users
				$query->where('userid', '=', 0);
			break;

			case 1:
			default:
				// Delete only logged out users and GET requests
				$query->where('userid', '=', 0);
				$query->where('transportmethod', '=', 'GET');
			break;
		}

		if ($debug)
		{
			$total = $query->count();
			$total = number_format($total);

			$this->line('Would delete ' . $total . ' record(s) from the log.');
			return;
		}

		$total = $query->delete();
		$total = number_format($total);

		if ($this->output->isVerbose())
		{
			$this->info('Deleted ' . $total . ' record(s) from the log.');
		}
	}
}
