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
use App\Modules\Resources\Entities\Asset;
use App\Modules\Users\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'courses:sync {--debug : Output actions it would take} {--log : Output is logged to the PHP error log}';

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

		$msg = __METHOD__ . '(): Starting sync ...';

		$this->info($msg);
		if ($log)
		{
			error_log($msg);
		}

		// Fetch a list of all classaccount IDs from the database.
		$courses = array();
		$errors = array();

		$classdata = Account::query()
			->withTrashed()
			->whereIsActive()
			->where('datetimestop', '>', Carbon::now()->toDateTimeString())
			->where('userid', '>', 0)
			->get();

		foreach ($classdata as $row)
		{
			// Fetch registerants
			event($event = new AccountInstructorLookup($row, $row->user));

			$row = $event->account;

			if ($row->cn)
			{
				$courses[] = $row;
			}
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

					$user = $event->user;

					if (!$user)
					{
						$msg = __METHOD__ . '(): Failed to retrieve user ID for organization_id ' . $student->externalId;

						$this->error($msg);
						if ($log)
						{
							error_log($msg);
						}
						continue;
					}
				}

				// Create a local entry, if one doesn't already exist
				$member = Member::query()
					->withTrashed()
					->whereIsActive()
					->where('classaccountid', '=', $course->id)
					->where('userid', '=', $userid)
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
						$msg = __METHOD__ . '(): Failed to create `classusers` entry for user #' . $user->id . ', class #' . $course->id;

						$this->error($msg);
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

			foreach ($row->members()->withTrashed()->whereIsActive()->get() as $extra)
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
			$u = new User;
			$u->username = $user;
			$u->primarygroup = 'student';
			$u->loginshell = '/bin/bash';
			$u->quota = 1;
			$u->pilogin = $user;

			// Get current status
			event($event = new ResourceMemberStatus($row->resource, $u));

			if ($event->status >= 400)
			{
				$msg = __METHOD__ . '(): Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;

				$this->error($msg);
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
				event($event = new ResourceMemberCreated($row->resource, $u));

				if ($event->status >= 400)
				{
					$msg = __METHOD__ . '(): Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;

					$this->error($msg);
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
					$msg = __METHOD__ . '(): Error getting AIMO ACMaint role info for ' . $user . ': ' . $event->status;

					$this->error($msg);
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
					event($event = new ResourceMemberCreated($fortress, $u));

					if ($event->status >= 400)
					{
						$msg = __METHOD__ . '(): Could not create AIMO ACMaint account for ' . $user . ': ' . $event->status;

						$this->error($msg);
						if ($log)
						{
							error_log($msg);
						}
						continue;
					}
				}
				else
				{
					$msg = __METHOD__ . '(): EXISTS ' . $user . ': ' . $event->status;

					$this->info($msg);
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

		$this->info(__METHOD__ . '(): ' . $msg);
		if ($log)
		{
			error_log($msg);
		}

		// Do some sanity checking
		// If our net loss here is greater than the new total, something is wrong
		if ((count($remove_users) - count($create_users)) > count($users))
		{
			// TODO: how can we detect and allow normal wipeage during semester turnover?
			$msg = __METHOD__ . '(): Deleting more users than we will have left. This seems wrong!';

			$this->error($msg);
			if ($log)
			{
				error_log($msg);
			}
			return;
		}

		$removed = array();
		foreach ($remove_users as $user)
		{
			if ($debug)
			{
				$msg = __METHOD__ . '(): Would delete AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;

				$this->info($msg);
				if ($log)
				{
					error_log($msg);
				}
				continue;
			}

			// Remove scholar
			event($event = new ResourceMemberDeleted($row->resource, $user));

			if ($event->status >= 400)
			{
				$msg = __METHOD__ . '(): Could not delete AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;

				$this->error($msg);
				if ($log)
				{
					error_log($msg);
				}
				continue;
			}

			if ($event->status)
			{
				$removed[] = $user;

				$msg = __METHOD__ . '(): Deleted AIMO ACMaint scholar role for ' . $user . ': ' . $event->status;

				$this->success($msg);
				if ($log)
				{
					error_log($msg);
				}
			}
		}

		$msg = __METHOD__ . '(): Finished syncing.';

		$this->info($msg);
		if ($log)
		{
			error_log($msg);
		}
	}
}
