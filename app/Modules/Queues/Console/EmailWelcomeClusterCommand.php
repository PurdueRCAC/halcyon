<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
//use Illuminate\Support\Fluent;
use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Models\User;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Entities\Child;
use App\Modules\Users\Models\User as SiteUser;
use App\Modules\Users\Events\UserBeforeDisplay;
use App\Modules\Queues\Mail\WelcomeCluster;

class EmailWelcomeClusterCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailwelcomecluster {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email welcome message to new cluster users.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$u = (new User)->getTable();
		$q = (new Queue)->getTable();
		$r = (new Child)->getTable();

		$users = User::query()
			->select($u . '.*', $q . '.groupid')
			->join($q, $q . '.id', $u . '.queueid')
			->whereIn($u . '.membertype', [1, 4])
			->whereIn($u . '.notice', [8, 13])
			->get();

		if (!count($users))
		{
			$this->comment('No records to email.');
			return;
		}

		// Group activity by userid
		$user_activity = array();
		foreach ($users as $user)
		{
			if (!isset($user_activity[$user->userid]))
			{
				$user_activity[$user->userid] = array();
			}

			array_push($user_activity[$user->userid], $user);
		}

		foreach ($user_activity as $userid => $userqueues)
		{
			$u = SiteUser::find($userid);

			if (!$u)
			{
				$this->error('Could not find account for user ID #' . $userid);
				continue;
			}

			event($event = new UserBeforeDisplay($u));

			$u = $event->getUser();

			$activity = array();

			foreach ($userqueues as $userqueue)
			{
				$queue = $userqueue->queue;

				if (!isset($activity[$queue->resource->id]))
				{
					$activity[$queue->resource->id] = new \stdClass; //new Fluent;
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
						->get()
						->first();
				}

				$activity[$queue->resource->id]->queues[] = $queue;
			}

			// Prepare and send actual email
			$message = new WelcomeCluster($u, $activity);

			if ($debug)
			{
				echo $message->render();
				continue;
			}

			Mail::to($u->email)->send($message);

			foreach ($userqueues as $userqueue)
			{
				$userqueue->update(['notice' => 0]);
			}

			$this->info("Emailed welcome (cluster) to {$user->email}.");
		}
	}
}
