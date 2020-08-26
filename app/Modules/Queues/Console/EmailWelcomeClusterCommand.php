<?php

namespace App\Modules\Queues\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use App\Modules\Queues\Models\Queue;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Mail\NewComment;
use App\Modules\Queues\Models\User;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Models\Child;
use App\Modules\Users\Models\User as SiteUser;

class EmailWelcomeClusterCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	//protected $name = 'queues:emailwelcomecluster';

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'queues:emailwelcomecluster {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email authorized queue access requests.';

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
			// convert time stamp to int
			//$user->datetimecreated = strtotime($user->datetimecreated);
			array_push($user_activity[$user->userid], $user);
		}

		$now = date("U");

		foreach ($user_activity as $userid => $userqueues)
		{
			$activity  = array();
			//$resources = array();
			//$frontends = array();

			//$last_cluster = '';

			//for ($i=0; $i < count($user); $i++)
			foreach ($userqueues as $userqueue)
			{
				$queue = $userqueue->queue;

				/*if (!isset($activity[$queue->resource->name]))
				{
					$activity[$queue->resource->name] = array(
						'queues' => array(),
						'standby' => array()
					);
				}

				$standby = Queue::query()
					->select($q . '.*')
					->join($r, $r . '.subresourceid', $q . '.subresourceid')
					->where($r . '.resourceid', '=', $queue->resource->id)
					->where(function($where)
						{
							$where->where($q . '.name', 'like', 'standby%')
								->orWhere($q . '.name', 'like', 'partner%');
						})
					->get();

				$activity[$queue->resource->name]['queues'][] = $queue;
				$activity[$queue->resource->name]['standby'] = $standby;

				if (isset($resources[$queue->resource->id]))
				{
					continue;
				}

				$resources[$queue->resource->id] = $queue->resource;*/

				if (!isset($activity[$queue->resource->id]))
				{
					$activity[$queue->resource->id] = new Fluent;
					$activity[$queue->resource->id]->resource = $queue->resource;
					$activity[$queue->resource->id]->queues   = array();
					//$activity[$queue->resource->id]->standby  = array();
					//$activity[$queue->resource->id]->storage  = array();

					$activity[$queue->resource->id]->standby = Queue::query()
						->select($q . '.*')
						->join($r, $r . '.subresourceid', $q . '.subresourceid')
						->where($r . '.resourceid', '=', $queue->resource->id)
						->where(function($where)
							{
								$where->where($q . '.name', 'like', 'standby%')
									->orWhere($q . '.name', 'like', 'partner%');
							})
						->get();

					$activity[$queue->resource->id]->storage = StorageResource::query()
						->where('parentresourceid', '=', $resource->id)
						->get()
						->first();
				}

				$activity[$queue->resource->id]['queues'][] = $queue;
			}

			/*$storages = array();
			foreach ($resources as $resource)
			{
				$storage = StorageResource::query()
					->where('parentresourceid', '=', $resource->id)
					->get()
					->first();

				if (!$storage)
				{
					continue;
				}

				$storages[$resource->name] = $storage;
			}*/

			$user = SiteUser::find($userid);

			// Prepare and send actual email
			if ($debug)
			{
				echo (new WelcomeMessage($user, $activity))->render();
				continue;
			}

			Mail::to($user->email)->send(new QueueAuthorized($user));

			$this->info("Emailed welcome (cluster) to {$user->email}.");
		}
	}
}
