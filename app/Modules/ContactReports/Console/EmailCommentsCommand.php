<?php

namespace App\Modules\ContactReports\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\History\Models\Log;
use App\Modules\ContactReports\Models\Comment;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Mail\NewComment;
use App\Modules\Users\Models\User;

class EmailCommentsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'crm:emailcomments {--debug : Output actions that would be taken without making them}';

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
		$debug = $this->option('debug') ? true : false;

		// Get all new comments
		$comments = Comment::where('notice', '!=', 0)->orderBy('id', 'desc')->get();

		if (!count($comments))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No new comments to email.');
			}
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

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("Email address not found for user {$user->name}.");
					}
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
					$message = new NewComment($comment);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->info("Emailed comment #{$comment->id} to {$user->email}.");
					}

					if ($debug)
					{
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $comment->id, $user->email, "Emailed comment #{$comment->id}.");
				}
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			foreach ($comments as $comment)
			{
				$comment->notice = 0;
				$comment->save();
			}
		}
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  null
	 */
	protected function log($targetuserid, $targetobjectid, $uri = '', $payload = '')
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => $uri,
			'app'             => 'email',
			'payload'         => $payload,
			'classname'       => 'crm:emailcomments',
			'classmethod'     => 'handle',
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
