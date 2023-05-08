<?php

namespace App\Modules\News\Console;

use Illuminate\Console\Command;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Type;
use Carbon\Carbon;

class CopyWeekCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'news:copyweek
		{start : Date to start on (YYYY-mm-dd)}
		{type=0 : News type ID}
		{weeks=4 : Number of days to copy to, counting from the start date}
		{--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Copy all news article starting on a specified date, for N weeks.';

	/**
	 * Execute the console command.
	 *
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;
		$start = $this->argument('start');
		$weeks = $this->argument('weeks');
		$weeks = $weeks ?: 4;

		if (!$start)
		{
			$start = Carbon::now()->startOfWeek();
		}
		else
		{
			$start = Carbon::parse($start)->startOfWeek();
		}

		$end = (clone $start)->modify('+1 week');

		if ($debug || $this->output->isVerbose())
		{
			$this->line('Copying articles for week of ' . $start->toDateTimeString());
		}

		$query = Article::query()
			->where('datetimenews', '>=', $start->toDateTimeString())
			->where('datetimenews', '<', $end->toDateTimeString());

		$typeid = $this->argument('type');
		if ($typeid)
		{
			$type = Type::find($typeid);

			if ($type)
			{
				$query->where('newstypeid', '=', $type->id);
			}
		}

		$articles = $query->get();

		if (!count($articles))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->line('No articles found for ' . $start);
			}
			return;
		}

		for ($i = 1; $i <= $weeks; $i++)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->line('Copying to week #' . $i);
			}

			foreach ($articles as $news)
			{
				$payload = new Article;
				$payload->datetimenews    = $news->datetimenews->modify('+' . $i . ' weeks');
				$payload->datetimenewsend = $news->datetimenewsend->modify('+' . $i . ' weeks');
				$payload->datetimecreated = Carbon::now()->format('Y-m-d h:m:s');
				$payload->userid          = $news->userid;
				$payload->edituserid      = $news->edituserid;
				$payload->published       = $news->published;
				$payload->headline        = $news->headline;
				$payload->body            = $news->body;
				$payload->location        = $news->location;
				$payload->template        = $news->template;
				$payload->newstypeid      = $news->newstypeid;
				$payload->url             = $news->url;

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Adding copy of #' . $news->id . ' for `' . $payload->datetimenews . '`');

					if ($debug)
					{
						continue;
					}
				}

				$payload->save();
			}
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->line('Finished.');
		}
	}
}
