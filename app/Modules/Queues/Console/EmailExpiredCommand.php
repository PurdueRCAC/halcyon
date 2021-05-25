<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Mail\Expired;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User as QueueUser;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Groups\Models\Group;
use Carbon\Carbon;

/**
 * Newly exipred users
 */
class EmailExpiredCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailexpired {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email expired accounts.';

	/**
	 * Execute the console command.
	 * 
	 * @return  void
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$uu = (new UserUsername)->getTable();
		$qu = (new QueueUser)->getTable();
		$q = (new Queue)->getTable();

		$users = QueueUser::query()
			->select($qu . '.*')
			->join($uu, $uu . '.userid', $uu . '.userid')
			->join($q, $q . '.id', $qu . '.queueid')
			->where(function($where) use ($q)
			{
				$where->whereNull($q . '.datetimeremoved')
					->orWhere($q . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($qu)
			{
				$where->whereNull($qu . '.datetimeremoved')
					->orWhere($qu . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($uu)
			{
				$now = Carbon::now();

				$where->whereNotNull($uu . '.dateremoved')
					->where($uu . '.dateremoved', '!=', '0000-00-00 00:00:00')
					->where($uu . '.dateremoved', '<', $now->toDateTimeString());
			})
			->groupBy($qu . '.id')
			->groupBy($qu . '.datetimecreated')
			->groupBy($qu . '.userid')
			->groupBy($q . '.groupid')
			->groupBy($uu . '.datecreated')
			->groupBy($uu . '.datelastseen')
			->orderBy($uu . '.datecreated', 'asc')
			->orderBy($uu . '.datelastseen', 'asc')
			->get();

		if (!count($users))
		{
			$this->comment('No records to email.');
			return;
		}

		// Group activity by groupid so we can determine when to send the group mail
		$group_activity = array();

		foreach ($users as $user)
		{
			if (!isset($group_activity[$user->groupid]))
			{
				$group_activity[$user->groupid] = array();
			}

			array_push($group_activity[$user->groupid], $user);
		}

		foreach ($group_activity as $groupid => $users)
		{
			$this->info("Starting processing group ID #{$groupid}.");

			// Find the latest activity
			$latest = 0;
			foreach ($users as $g)
			{
				if ($g->datetimecreated->format('U') > $latest)
				{
					$latest = $g->datetimecreated->format('U');
				}
			}

			$group = Group::find($groupid);

			if (!$group)
			{
				continue;
			}

			foreach ($group->managers as $manager)
			{
				// Prepare and send actual email
				$message = new Expired($manager->user, $users);

				if ($debug)
				{
					echo $message->render();
					continue;
				}

				Mail::to($manager->user->email)->send($message);

				$this->info("Emailed expired to manager {$manager->user->email}.");
			}
		}

		$this->info("Finished processing group ID #{$group}.");
	}
}
