<?php

namespace App\Modules\Courses\Console;

use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Courses\Events\AccountLookup;
use App\Modules\Courses\Events\AccountInstructorLookup;
use App\Modules\Courses\Events\AccountEnrollment;
use App\Modules\Courses\Events\CourseEnrollment;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Models\Asset;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Events\UserLookup;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'courses:sync {--debug : Output actions that would be taken without making them} {--log : Output is logged to the PHP error log}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync class account roster.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;
		$log   = $this->option('log') ? true : false;

		$msg = 'Starting class sync.';
		if ($debug || $this->output->isVerbose())
		{
			$this->info($msg);
		}
		if ($log)
		{
			error_log($msg);
		}

		// Fetch a list of all classaccount IDs from the database.
		$courses = array();
		$errors = array();

		$classdata = Account::query()
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->where('userid', '>', 0)
			->get();

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Looking up instructor class info ...');
		}

		foreach ($classdata as $row)
		{
			if (!$row->user)
			{
				$msg = 'Failed to retrieve instructor for class #' . $row->id;
				if ($debug || $this->output->isVerbose())
				{
					$this->error($msg);
				}
				if ($log)
				{
					error_log($msg);
				}
				continue;
			}

			// Fetch registerants
			event($event = new AccountInstructorLookup($row, $row->user));

			$row = $event->account;

			if ($row->classid)
			{
				$courses[] = $row;
			}
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Looking up enrollment info for each class ...');
		}

		// Fetch course enrollments
		$students = array();
		foreach ($courses as $course)
		{
			event($event = new AccountEnrollment($course));

			$course = $event->account;
			$count  = 0;

			foreach ($event->enrollments as $student)
			{
				$user = User::query()
					->where('puid', '=', $student->externalId)
					->limit(1)
					->first();

				if (!$user)
				{
					// Nope, sorry. Look them up and post.
					event($event = new UserLookup(['puid' => $student->externalId]));

					$user = !empty($event->results) ? $event->results[0] : null;

					if (!$user)
					{
						$msg = 'Failed to retrieve user ID for puid ' . $student->externalId;
						if ($debug || $this->output->isVerbose())
						{
							$this->error($msg);
						}
						if ($log)
						{
							error_log($msg);
						}
						continue;
					}

					// Create an account if none exist
					if (!$user->id)
					{
						$user->puid = $student->externalId;
						$user->save();

						$userusername = new UserUsername;
						$userusername->userid = $user->id;
						$userusername->username = $user->username;
						$userusername->save();
					}
				}

				// Create a local entry, if one doesn't already exist
				$member = Member::query()
					->where('classaccountid', '=', $course->id)
					->where('userid', '=', $user->id)
					->first();

				if (!$member)
				{
					$member = new Member();
					$member->userid         = $user->id;
					$member->datetimestart  = $course->datetimestart;
					$member->datetimestop   = $course->datetimestop;
					$member->classaccountid = $course->id;
					$member->notice         = 0;
					$member->membertype     = 0; // 0 = autocreated, 1 = explicit

					if (!$member->save())
					{
						$msg = 'Failed to create `classusers` entry for user #' . $user->id . ', class #' . $course->id;
						if ($debug || $this->output->isVerbose())
						{
							$this->error($msg);
						}
						if ($log)
						{
							error_log($msg);
						}
						continue;
					}
				}

				$count++;

				$students[] = $user;
			}

			// Slap student count back into database
			unset($course->classid);
			$course->update([
				'studentcount' => $count
			]);
		}

		$users = array();
		$now = Carbon::now();

		// Ok, we got students. Search for students that need access now.
		foreach ($students as $student)
		{
			if ($student->datetimestart <= $now->toDateTimeString()
			 && $student->datetimestop > $now->toDateTimeString())
			{
				$users[] = $student->username;
			}
		}

		// OK! Now we need to get explict users. TAs, instructors, and workshop participants.
		foreach ($classdata as $row)
		{
			// Add instructor starting now
			$users[] = $row->user ? $row->user->username : $row->userid;

			foreach ($row->members as $extra)
			{
				$users[] = $extra->user ? $extra->user->username : $extra->userid;
			}
		}

		$users = array_unique($users);

		// Get list of current scholar users
		event($event = new CourseEnrollment($users));

		$create_users = $event->create_users;
		$remove_users = $event->remove_users;


		$fortress = Asset::findByName('HPSSUSER');

		$created = array();
		foreach ($create_users as $user)
		{
			$u = User::findByUsername($user);
			if (!$u)
			{
				$u = new User;
			}
			$u->username = $user;
			$u->primarygroup = 'student';
			$u->loginShell = '/bin/bash';
			$u->quota = 1;
			$u->pilogin = $user;

			// Get current status
			event($event = new ResourceMemberStatus($row->resource, $u));

			if ($event->status >= 400)
			{
				$msg = 'Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;

				if ($debug || $this->output->isVerbose())
				{
					$this->error($msg);
				}
				if ($log)
				{
					error_log($msg);
				}
				continue;
			}

			// Is status is pending or ready
			// If $event->status == 1, then no role currently exists
			if ($event->status != 2
			 && $event->status != 3)
			{
				// Create account
				event($event = new ResourceMemberCreated($row->resource, $u));

				if ($event->status >= 400)
				{
					$msg = 'Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;
					if ($debug || $this->output->isVerbose())
					{
						$this->error($msg);
					}
					if ($log)
					{
						error_log($msg);
					}
					continue;
				}
			}

			// Create Fortress
			if ($fortress)
			{
				event($event = new ResourceMemberStatus($fortress, $u));

				if ($event->status >= 400)
				{
					$msg = 'Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;
					if ($debug || $this->output->isVerbose())
					{
						$this->error($msg);
					}
					if ($log)
					{
						error_log($msg);
					}
					continue;
				}

				// Is status is pending or ready
				if ($event->status != 2
				 && $event->status != 3)
				{
					// Create account
					if ($debug)
					{
						$this->info('Would create AIMO ACMaint account for ' . $user);
						continue;
					}

					event($event = new ResourceMemberCreated($fortress, $u));

					if ($event->status >= 400)
					{
						$msg = 'Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;
						if ($debug || $this->output->isVerbose())
						{
							$this->error($msg);
						}
						if ($log)
						{
							error_log($msg);
						}
						continue;
					}
				}
				else
				{
					$msg = 'AIMO ACMaint account already exists for ' . $user . ': ' . $event->status;

					if ($debug || $this->output->isVerbose())
					{
						$this->info($msg);
					}
					if ($log)
					{
						error_log($msg);
					}
				}
			}

			if ($event->status)
			{
				$created[] = $user;
			}
		}

		$data = array(
			'Creating: ' . count($create_users),
			'Removing: ' . count($remove_users),
			'Total users: ' . count($users)
		);
		$msg = implode(', ', $data);

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Class sync - ' . $msg);
		}
		if ($log)
		{
			error_log($msg);
		}

		// Do some sanity checking
		// If our net loss here is greater than the new total, something is wrong
		if ((count($remove_users) - count($create_users)) > count($users))
		{
			// TODO: how can we detect and allow normal wipeage during semester turnover?
			$msg = 'Deleting more users than we will have left. This seems wrong! Removing ' . count($remove_users) . ' of ' . count($users) . ' total.';
			if ($debug || $this->output->isVerbose())
			{
				$this->error($msg);
			}
			if ($log)
			{
				error_log($msg);
			}
			return;
		}

		$removed = array();
		foreach ($remove_users as $user)
		{
			$msg = 'Would delete AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;

			if ($debug || $this->output->isVerbose())
			{
				$this->info($msg);
			}

			if ($log)
			{
				error_log($msg);
			}

			if ($debug)
			{
				continue;
			}

			$u = User::findByUsername($user);

			// Remove scholar
			event($event = new ResourceMemberDeleted($row->resource, $u));

			if ($event->status >= 400)
			{
				$msg = 'Could not delete AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;
				if ($debug || $this->output->isVerbose())
				{
					$this->error($msg);
				}
				if ($log)
				{
					error_log($msg);
				}
				continue;
			}

			if ($event->status)
			{
				$removed[] = $user;

				$msg = 'Deleted AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;
				if ($debug || $this->output->isVerbose())
				{
					$this->success($msg);
				}
				if ($log)
				{
					error_log($msg);
				}
			}
		}

		$msg = 'Finished class sync.';

		if ($debug || $this->output->isVerbose())
		{
			$this->info($msg);
		}
		if ($log)
		{
			error_log($msg);
		}
	}
}
