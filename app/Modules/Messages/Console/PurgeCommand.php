<?php

namespace App\Modules\Messages\Console;

use Illuminate\Console\Command;
use App\Modules\Messages\Models\MEssage;
use Carbon\Carbon;

class PurgeCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'messages:purge
							{type? : (optional) Limit to entries for a specific type}
							{--days=365 : Number of days to retain logs}
							{--level=1 : Level 1) Delete only successful runs, Level 2) Delete only unsuccessful runs, Level 3) Delete all}
							{--debug : (optional) Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Purge message queue entries older than the specified days.';

	/**
	 * Execute the console command.
	 *
	 * @return  void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$type = $this->argument('type');

		$level = $this->option('level');
		$level = $level ?: 1;

		$maxAge = $this->option('days');
		$maxAge = $maxAge ?: config('module.messages.max_age', 365);

		$cutOffDate = Carbon::now()
			->subDays($maxAge)
			->format('Y-m-d H:i:s');

		$query = Message::query()
			->where('datetimesubmitted', '<', $cutOffDate)
			->whereNotNull('datetimecompleted');

		if ($type)
		{
			$query->where('messagequeuetypeid', '=', $type);
		}

		switch ($level)
		{
			case 3:
				// Delete everything
			break;

			case 2:
				$query->where('returnstatus', '>', 0);
			break;

			case 1:
			default:
				$query->where('returnstatus', '=', 0);
			break;
		}

		if ($debug || $this->output->isVerbose())
		{
			$total = $query->count();
			$total = number_format($total);

			$this->line('Would delete ' . $total . ' record(s) from the message queue.');
			return;
		}

		$total = $query->delete();
		$total = number_format($total);

		$this->info('Deleted ' . $total . ' record(s) from the message queue.');
	}
}
