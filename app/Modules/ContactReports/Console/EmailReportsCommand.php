<?php

namespace App\Modules\ContactReports\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\ContactReports\Mail\NewReport;
use App\Modules\ContactReports\Models\Report;
use App\Modules\Users\Models\User;

class EmailReportsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'crm:emailreports {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest Contact Reports to subscribers.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		// Get all new comments
		$reports = Report::where('notice', '!=', 0)
			->orderBy('id', 'asc')
			->get();

		if (!count($reports))
		{
			if ($debug)
			{
				$this->comment('No new reports to email.');
			}
			return;
		}

		$users = [];

		if ($role = config('module.contactreports.admin_role'))
		{
			$users = User::findByRole($role)->pluck('id')->toArray();
		}

		foreach ($reports as $report)
		{
			$emailed = array();

			$subscribers = $report->subscribers();
			$subscribers = array_merge($subscribers, $users);
			array_filter($subscribers);

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				if (in_array($subscriber, $emailed))
				{
					continue;
				}

				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$emailed[] = $user->id;

				$message = new NewReport($report);
				$message->headers()->text([
					'X-Command' => 'crm:emailreports',
					'X-Target-User' => $user->id
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed contact report #{$report->id} to {$user->email}.");
				}

				if ($debug)
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

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$report->notice = 0;
			$report->save();
		}
	}
}
