<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User;
use App\Modules\Queues\Models\GroupUser;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Models\Child;
use App\Modules\Users\Models\User as SiteUser;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Queues\Mail\WelcomeFree;

class EmailWelcomeFreeCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailwelcomefree {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email welcome message to new free resource users.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$gu = (new GroupUser)->getTable();
		$u = (new User)->getTable();
		$q = (new Queue)->getTable();
		$r = (new Child)->getTable();

		$users = User::query()
			->select($gu . '.*', $u . '.queueid')
			->join($gu, $gu . '.queueuserid', $u . '.id')
			->join($q, $q . '.id', $u . '.queueid')
			->whereIn($gu . '.membertype', [1, 4])
			->whereIn($gu . '.notice', [8, 13])
			->get();

		if (!count($users))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->comment('No records to email.');
			}
			return;
		}

		// Group activity by userid
		$user_activity = array();
		foreach ($users as $user)
		{
			if (!isset($user_activity[$user->queueuserid]))
			{
				$user_activity[$user->queueuserid] = array();
			}

			array_push($user_activity[$user->queueuserid], $user);
		}

		foreach ($user_activity as $userid => $userqueues)
		{
			$u = SiteUser::find($userid);

			if (!$u)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find account for user ID #' . $userid);
				}
				continue;
			}

			event($event = new UserBeforeDisplay($u));

			$u = $event->getUser();

			// Check login shell
			if ($u->loginShell)
			{
				if (!file_exists($u->loginShell))
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Login Shell ' . $u->loginShell . ' is invalid.');
					}
					continue;
				}
			}
			else
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Login Shell is not set for user ID #' . $userid);
				}
				continue;
			}

			// Check home directory
			/*if ($u->homeDirectory)
			{
				if (!file_exists($u->homeDirectory))
				{
					$this->error('Home directory ' . $homeDirectory . ' does not exist.');
					continue;
				}
			}
			else
			{
				$this->error('Home directory is not set for user ID #' . $userid);
				continue;
			}*/

			$activity  = array();

			foreach ($userqueues as $userqueue)
			{
				$queue = $userqueue->queue;

				if (!isset($activity[$queue->resource->id]))
				{
					$activity[$queue->resource->id] = new \stdClass;
					$activity[$queue->resource->id]->resource = $queue->resource;
					$activity[$queue->resource->id]->queues   = array();

					$activity[$queue->resource->id]->standbys = Queue::query()
						->select($q . '.*')
						->join($r, $r . '.subresourceid', $q . '.subresourceid')
						->where($r . '.resourceid', '=', $queue->resource->id)
						->where(function($where) use ($q)
							{
								$where->where($q . '.name', 'like', 'standby%')
									->orWhere($q . '.name', 'like', 'partner%');
							})
						->get();

					$activity[$queue->resource->id]->storage = StorageResource::query()
						->where('parentresourceid', '=', $queue->resource->id)
						->first();
				}

				$activity[$queue->resource->id]->queues[] = $queue;
			}

			// Prepare and send actual email
			$message = new WelcomeFree($u, $activity);
			$message->headers()->text([
				'X-Command' => 'queues:emailwelcomefree',
			]);

			if ($this->output->isDebug())
			{
				echo $message->render();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->info("Emailed welcome (free) to {$u->email}.");

				if ($debug)
				{
					continue;
				}
			}

			if ($u->email)
			{
				Mail::to($u->email)->send($message);
			}
			else
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error("Email address not found for user {$u->name}.");
				}
			}

			foreach ($userqueues as $userqueue)
			{
				$userqueue->update(['notice' => 0]);
			}
		}
	}
}
