<?php

namespace App\Modules\ContactReports\Console;

use App\Modules\ContactReports\Mail\NewReport;
use App\Modules\ContactReports\Models\Report;
use App\Modules\Users\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
			$this->comment('No new reports to email.');
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

				if (!$user)
				{
					continue;
				}

				// Prepare and send actual email
				$emailed[] = $user->id;

				$message = new NewReport($report);

				if ($debug)
				{
					echo $message->render();
					continue;
				}

				Mail::to($user->email)->send($message);

				$this->info("Emailed report #{$report->id} to {$user->email}.");
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
