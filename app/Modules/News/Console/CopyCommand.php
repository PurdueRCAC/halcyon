<?php

namespace App\Modules\News\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\News\Models\Article;
use DateTime;

class CopyCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'news:copy {id} {start} {days=4} {--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Copy a news article starting on a specified date, for N days.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$id = $this->argument('id');

		$news = Article::find($id);

		if (!$news || !$news->id)
		{
			$this->danger('Failed to find news entry for ID #' . $id);
			return;
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->line('Copying ID #' . $id . ' ...');
		}

		$week_start = $this->argument('start');

		if (!$week_start)
		{
			$day = date('w');
			$week_start = (new DateTime('now'))->modify('-' . $day . ' days');
			$week_end   = (new DateTime('now'))->modify('+' . (6 - $day) . ' days');
		}
		else
		{
			$week_start = new DateTime($week_start);
		}
		$week_end = (new DateTime('now'))->modify('+6 days');

		$start = $week_start;
		$end   = $week_end->modify('-2 days');

		$days  = $this->argument('days');
		$days  = $days ?: 4;

		for ($i = 1; $i <= $days; $i++)
		{
			$times = array(
				array(
					'start' => '14:00:00',
					'end' => '14:30:00'
				),
				array(
					'start' => '14:30:00',
					'end' => '15:00:00'
				),
				array(
					'start' => '15:00:00',
					'end' => '15:30:00'
				),
			);

			$start = $week_start->modify('+1 day');

			foreach ($times as $range)
			{
				$payload = new Article;
				$payload->datetimenews    = $start->format('Y-m-d') . ' ' . $range['start'];
				$payload->datetimenewsend = $start->format('Y-m-d') . ' ' . $range['end'];
				$payload->datetimecreated = (new DateTime('now'))->format('Y-m-d h:m:s');
				$payload->userid          = $news->userid;
				$payload->edituserid      = $news->edituserid;
				$payload->published       = 1;
				$payload->headline        = $news->headline;
				$payload->body = $news->body;
				$payload->location = $news->location;
				$payload->template = $news->template;
				$payload->newstypeid = $news->newstypeid;
				$payload->url = $news->url;

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Adding copy of #' . $id . ' for `' . $payload->datetimenews . '`');
				}

				if ($debug)
				{
					continue;
				}

				$payload->save();
			}
		}
	}
}
