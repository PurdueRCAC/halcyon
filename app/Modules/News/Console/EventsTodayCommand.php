<?php

namespace App\Modules\News\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Modules\News\Models\Type;
use App\Modules\News\Notifications\EventRegistered;
use App\Modules\News\Notifications\EventClaimed;
use App\Modules\Users\Events\UserBeforeDisplay;
use Carbon\Carbon;

class EventsTodayCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'news:eventstoday {type} {--summary : Output a summary of claimed events} {--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Post a schedule for the day\'s events.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;
		$summary = $this->option('summary') ? true : false;

		$id = $this->argument('type');

		$type = Type::find($id);

		if (!$type || !$type->id)
		{
			$this->error('Failed to find news type for ID #' . $id);
			return;
		}

		$route = env('SLACK_NOTIFICATION_NEWS_TODAY');

		if (!$route)
		{
			$this->error('Slack notification webhook is not configured');
			return;
		}

		$week_start = Carbon::now();
		$week_end   = Carbon::now()->modify('+' . 1 . ' days');

		$start = $week_start->format('Y-m-d') . ' 00:00:00';
		$stop  = $week_end->format('Y-m-d') . ' 00:00:00';

		$rows = $type->articles()
			->with('associations')
			->where('published', '=', 1)
			->where('template', '=', 0)
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '<=', $start)
					->orWhere('datetimenews', '<=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenewsend', '>=', $start)
					->orWhere('datetimenewsend', '>=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '<=', $start)
					->orWhere('datetimenews', '<=', $stop)
					->orWhereNotNull('datetimenewsend');
			})
			->where(function($where) use ($start, $stop)
			{
				$where->where('datetimenews', '>=', $start)
					->orWhere('datetimenews', '>=', $stop)
					->orWhereNull('datetimenewsend');
			})
			->orderBy('datetimenews', 'asc')
			->limit(100)
			->get();

		$locations = array();
		foreach ($rows as $event)
		{
			if (!count($event->associations))
			{
				continue;
			}

			if (!isset($locations[$event->location]))
			{
				$locations[$event->location] = $event;
			}

			$assoc = $event->associations->first();
			$user = $assoc->associated;

			if (!$user)
			{
				$this->error('Could not find account for user ID ' . $assoc->associd);
			}

			if ($debug)
			{
				event($e = new UserBeforeDisplay($user));
				$user = $e->getUser();

				$groups = array();
				foreach ($user->groups as $g)
				{
					$groups[] = $g->group->name;
				}

				$this->line($event->datetimenews->format('g:ia') . ' - ' . $event->datetimenewsend->format('g:ia T') . ':');

				$message  = "\n    Location: " . $event->location;
				$message .= "\n    URL: " . $event->url;
				$message .= "\n    Name: " . $user->name . ' (' . $user->username . ')';
				$message .= "\n    Department: " . ($user->department ? $user->department : '-');
				$message .= "\n    Groups: " . (count($groups) ? implode(', ', $groups) : '-');
				$message .= "\n    Reason: " . $assoc->comment;
				$message .= "\n";

				$this->comment($message);

				continue;
			}

			if ($this->output->isVerbose())
			{
				$this->line('Sending notification for event #' . $event->id);
			}

			Notification::route('slack', $route)
				->notify($summary ? new EventClaimed($event) : new EventRegistered($event));
		}
	}
}
