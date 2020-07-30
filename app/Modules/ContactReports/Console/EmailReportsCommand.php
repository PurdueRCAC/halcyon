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
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'crm:emailreports';

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
		// Get all new comments
		//$reports = Report::where('notice', '=', 23)->get();
		$reports = Report::where('notice', '!=', 0)->get();

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
				//Mail::to($user->email)->send(new NewReport($report));
				echo (new NewReport($report))->render();

				$emailed[] = $user->id;

				$this->info("Emailed report #{$report->id} to {$user->email}.");
			}

			// Change states
			$report->notice = 0;
			$report->save();
		}
	}
}
