<?php

namespace App\Modules\Groups\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\User;

class EmailRemovedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'groups:emailremoved';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest Contact Report comments to subscribers.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		// Get all new comments
		$comments = Comment::where('notice', '!=', 0)->get();
		//$comments = Comment::where('notice', '=', 22)->get();

		if (!count($comments))
		{
			$this->comment('No new comments to email.');
			return;
		}

		// Group activity by report so we can determine when to send the report mail
		$report_activity = array();
		foreach ($comments as $comment)
		{
			if (!isset($report_activity[$comment->contactreportid]))
			{
				$report_activity[$comment->contactreportid] = array();
			}

			array_push($report_activity[$comment->contactreportid], $comment);
		}

		foreach ($report_activity as $report_id => $comments)
		{
			// Email everyone involved in this report

			// Assemble list of people to email
			$report = Report::find($report_id);
			$subscribers = $report->commentSubscribers();

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user)
				{
					continue;
				}

				// Start assembling email
				foreach ($comments as $comment)
				{
					// Ignore if the subscriber is the commenter
					if ($comment->userid == $subscriber)
					{
						continue;
					}

					// Prepare and send actual email
					//Mail::to($user->email)->send(new NewComment($comment));
					//echo (new NewComment($comment))->render();

					$this->info("Emailed comment #{$comment->id} to {$user->email}.");
				}
			}

			// Change states
			foreach ($comments as $comment)
			{
				$comment->notice = 0;
				$comment->save();
			}
		}
	}
}
