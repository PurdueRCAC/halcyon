<?php

namespace App\Modules\Users\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
//use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;

class CleanUpCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'users:cleanup {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync user accounts with external sources.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		//$a = (new User)->getTable();
		$u = (new UserUsername)->getTable();

		$subitems = UserUsername::query()
			->select('userid', DB::raw('COUNT(*)'))
			->withTrashed()
			->groupBy('userid')
			->having(DB::raw('COUNT(*)'), '>', 1)
			->limit(500)
			->get();

		/*$users = UserUsername::query()
			->select($a . '.*')
			->joinSub($subitems, 'b', function ($join) {
				$join->on($a . '.userid', '=', 'b.userid');
			})
			->orderBy($a . '.userid', 'asc')
			->get();*/

		if (!count($subitems))
		{
			if ($debug)
			{
				$this->info('No users found');
			}
			return;
		}

		if ($debug)
		{
			$this->info('Found ' . count($subitems) . ' users with multiple usernames');
		}

		foreach ($subitems as $u)
		{
			$users = UserUsername::query()
				->withTrashed()
				->where('userid', '=', $u->userid)
				->orderBy('userid', 'asc')
				->get();

			$first = $users->first();

			foreach ($users as $user)
			{
				if ($first && $user->id == $first->id)
				{
					continue;
				}

				if ($first && $user->username != $first->username)
				{
					$this->comment('Alternate username for user ID #' . $user->userid . ' (' . $first->username . ' / ' . $user->username . '). Skipping...');
					continue;
				}

				if ($user->trashed())
				{
					if (!$debug)
					{
						$user->forceDelete();
					}
					$this->line('<fg=red>Removed trashed duplicate (' . $user->username . ', ' . $user->userid . ') #' . $user->id . '</>');
					continue;
				}

				if ($user->datecreated > $first->datelastseen)
				{
					$first->datelastseen = $user->datecreated;
				}
				if ($user->datelastseen > $first->datelastseen)
				{
					$first->datelastseen = $user->datelastseen;
				}
				if ($user->unixid && !$first->unixid)
				{
					$first->unixid = $user->unixid;
				}

				if ($first && $first->trashed())
				{
					$first->dateremoved = null;
					//$first->restore();
					$this->info('Restoring original (' . $user->username . ', ' . $user->userid . ') #' . $user->id);
				}

				if (!$debug)
				{
					if ($first)
					{
						$first->save();
					}
					$user->forceDelete();
				}

				$this->line('<fg=red>Removed duplicate (' . $user->username . ', ' . $user->userid . ') #' . $user->id . '</>');
			}
		}
	}
}
