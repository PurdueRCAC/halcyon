<?php

namespace App\Modules\History\Console;

use Illuminate\Console\Command;
use App\Modules\History\Models\History;
use Carbon\Carbon;

class PurgeHistoryCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'history:purge
							{table? : (optional) Limit to entries for a specific table}
							{--days : Number of days to retain history}
							{--debug : (optional) Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Purge change history entries older than the specified days.';

	/**
	 * Execute the console command.
	 *
	 * @return  void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$table = $this->argument('table');

		$maxAge = $this->option('days');
		$maxAge = $maxAge ?: config('module.history.max_history_age');

		if (!$maxAge)
		{
			$this->error('No days specified.');
			return;
		}

		$cutOffDate = Carbon::now()
			->subDays($maxAge)
			->format('Y-m-d H:i:s');

		$query = History::query()
			->where('created_at', '<', $cutOffDate);

		if ($table)
		{
			$query->where('historable_table', '=', $table);
		}

		if ($debug)
		{
			$total = $query->count();
			$total = number_format($total);

			$this->line('Would delete ' . $total . ' record(s) from the history.');
			return;
		}

		$total = $query->delete();
		$total = number_format($total);

		if ($this->output->isVerbose())
		{
			$this->info('Deleted ' . $total . ' record(s) from the history.');
		}
	}
}
