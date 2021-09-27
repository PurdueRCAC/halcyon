<?php

namespace App\Modules\Courses\Console;

use App\Modules\Courses\Mail\Added;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class EmailAdditionsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'courses:emailadditions {--debug : Output actions that would be taken without making them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email requests to add users to a course.';

	/**
	 * Execute the console command.
	 * 
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$members = Member::query()
			->where('notice', '=', 1)
			->orderBy('id', 'asc')
			->get();

		if (!count($members))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No new additions to email.');
			}
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$class_activity = array();
		foreach ($members as $user)
		{
			if (!isset($class_activity[$user->classaccountid]))
			{
				$class_activity[$user->classaccountid] = array();
			}

			array_push($class_activity[$user->classaccountid], $user);
		}

		$now = date("U");
		$threshold = 1200; // threshold for when considering activity "done"

		foreach ($class_activity as $courseid => $accounts)
		{
			// Find the latest activity
			$latest = 0;
			foreach ($accounts as $g)
			{
				if ($g->datetimecreated->timestamp > $latest)
				{
					$latest = $g->datetimecreated->timestamp;
				}
			}

			if ($now - $latest < $threshold)
			{
				continue;
			}

			$course = Account::find($courseid);

			if (!$course)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find course account #' . $courseid);
				}
				continue;
			}

			$user = User::find($course->userid);

			if (!$user)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find instructor for course account #' . $courseid);
				}
				continue;
			}

			// Prepare and send actual email
			$message = new Added($user, $course, $accounts);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info("Emailed course additions to {$user->email}.");
			}

			if ($debug)
			{
				continue;
			}

			Mail::to($user->email)->send($message);

			// Change states
			$course->update(['notice' => 0]);

			foreach ($accounts as $account)
			{
				$account->update(['notice' => 0]);
			}
		}
	}
}
