<?php

namespace App\Modules\Users\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Modules\Users\Events\UserLookup;
use App\Modules\Users\Models\User;
use App\Modules\Users\Models\UserUsername;

class SyncCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'users:sync {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync user accounts with external sources.';

	/**
	 * Execute the console command.
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$a = (new User)->getTable();
		$u = (new UserUsername)->getTable();

		$users = User::query()
			->select($a . '.id', $u . '.username')
			->join($u, $u . '.userid', $a . '.id')
			->whereNull($u . '.dateremoved')
			/*->where(function($where) use ($a)
			{
				$where->where($a . '.puid', '=', 0)
					->orWhereNull($a . '.api_token')
					->orWhere($a . '.api_token', '=', '');
			})*/
			->where($a . '.puid', '=', 0)
			->get();

		if (!count($users))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No users found');
			}
			return;
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Syncing ' . count($users) . ' users');
		}

		foreach ($users as $u)
		{
			$update = false;

			event($e = new UserLookup(['username' => $u->username]));

			if (empty($e->results[0]))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment('No lookup results for #' . $u->id . ' (' . $u->username . '). Skipping...');
				}
				continue;
			}

			$user = $e->results[0];

			/*if (!$user->api_token)
			{
				$user->api_token = Str::random(60);

				$update = true;

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Updating api_token for #' . $user->id . ' (' . $user->username . ')');
				}
			}*/

			if ($user->puid && !$user->getOriginal('puid'))
			{
				$update = true;

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Updating puid for #' . $user->id . ' (' . $user->username . ')');
				}
			}
			else
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->info('No change in puid for #' . $user->id . ' (' . $user->username . ')');
				}
			}

			if ($update)
			{
				$user->save();
			}
		}
	}
}
