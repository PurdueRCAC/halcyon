<?php

namespace App\Listeners\Queues\Slurm;

use App\Modules\Queues\Models\Queue;
use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\UserCreated as QueueUserCreated;
use App\Modules\Queues\Events\UserDeleted as QueueUserDeleted;
use App\Modules\Queues\Events\QosCreated;
use App\Modules\Queues\Events\QosUpdated;
use App\Modules\Queues\Events\QosDeleted;
use App\Modules\Queues\Events\QueueSizeCreated;
use App\Modules\Queues\Events\QueueSizeUpdated;
use App\Modules\Queues\Events\QueueSizeDeleted;
use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Queues\Events\QueueLoanUpdated;
use App\Modules\Queues\Events\QueueLoanDeleted;
use App\Modules\Queues\Events\Schedule;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\History\Traits\Loggable;
use App\Modules\Users\Models\User;
use GuzzleHttp\Client;

/**
 * Slurm listener
 */
class Slurm
{
	use Loggable;

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		// Create/update/delete Slurm accounts
		//$events->listen(QueueCreated::class, self::class . '@handleQueueCreated');
		//$events->listen(QueueDeleted::class, self::class . '@handleQueueDeleted');

		$events->listen(QosCreated::class, self::class . '@handleQos');
		$events->listen(QosUpdated::class, self::class . '@handleQos');
		$events->listen(QosDeleted::class, self::class . '@handleQosDeleted');

		// Update Slurm account associations
		/*
		$events->listen(QueueUserCreated::class, self::class . '@handleQueueUserCreated');
		$events->listen(QueueUserDeleted::class, self::class . '@handleQueueUserDeleted');

		// Update Slurm account resource limits
		$events->listen(QueueSizeCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeDeleted::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanDeleted::class, self::class . '@handleQueueAllocation');
		*/

		$events->listen(Schedule::class, self::class . '@handleSchedule');
	}

	/**
	 * Check if this listener should handle this Queue
	 *
	 * @param  Queue $queue
	 * @return bool|array
	 */
	private function canProcessQueue($queue)
	{
		if (!$queue)
		{
			return false;
		}

		if (!$queue->scheduler
		 || !$queue->scheduler->resource
		 || !$queue->scheduler->resource->rolename)
		{
			return false;
		}

		$facet = $queue->scheduler->resource->getFacet('slurmapi');

		if (!$facet || !$facet->value || strtolower($facet->value) == 'no')
		{
			return false;
		}

		$config = config('listener.slurm', []);

		if (empty($config))
		{
			return false;
		}

		$config['url'] = $config['domain'] . ($config['port'] ? ':' . $config['port'] : '') . '/slurmdb/' . $config['version'];

		return $config;
	}

	/**
	 * Get the client
	 *
	 * @param  array $config
	 * @return Client
	 */
	private function getClient($config)
	{
		return new Client([
			'headers' => [
				$config['username_header'] => $config['username'],
				$config['token_header'] => $config['token']
			],
		]);
	}

	/**
	 * Retrieve a Slurm Account
	 *
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @return array
	 */
	private function getAccount(Client $client, array $config, Queue $queue)
	{
		try
		{
			$res = $client->request('GET', $config['url'] . '/account/' . $queue->name);

			if ($res->getStatusCode() == 404)
			{
				return null;
			}
			elseif ($res->getStatusCode() >= 400)
			{
				throw new \Exception('Slurm API: Failed to retrieve account for queue "' . $queue->name . '"', $res->getStatusCode());
			}

			$data = json_decode($res->getBody()->getContents());
		}
		catch (\GuzzleHttp\Exception\ServerException $e)
		{
			$data = json_decode($e->getResponse()->getBody()->getContents());
		}

		$account = null;
		if (isset($data->accounts) && !empty($data->accounts))
		{
			$account = $data->accounts[0];
		}

		return $account;
	}

	/**
	 * Convert values to Slurm
	 *
	 * @param Queue $queue
	 * @return array
	 */
	private function toSlurm(Queue $queue)
	{
		/*
		slurm_rosetta = {
			"Partition": "cluster",
			"Description": "description",
			"Account": "queue",
			"User": "acl_users",
			"Priority": "Priority",
			"MaxJobs": "max_user_run",
			"GrpJobs": "max_running",
			"GrpSubmit": "max_queuable",
			"MaxSubmitJobs": "max_user_queuable",
			"MaxSubmit": "max_user_queuable",
			"MaxWall": "resources_max.walltime",
			"DefaultAccount": "default_account",
			"GrpTRES": {
				"bb" : "slurm.bb", # Burst Buffers
				"billing": "slurm.billing", # Billing
				"cpu": "resources_max.ncpus", # num CPU
				"energy": "slurm.energy", # energy
				"fs": "slurm.fs", # filesystems - <disk,lustre>
				"gres/gpu": "slurm.gpu", # gpus
				"ic": "slurm.ic", # interconnect - <ofed>
				"license": "slurm.license", # licenses
				"mem": "slurm.mem", # memory
				"node": "slurm.node", # node
				"pages": "slurm.pages", # pages
				"vmem": "slurm.vmem" # virtual memory
			}
		}
		*/
		if (!$queue->enabled)
		{
			$tres['max_user_run'] = '0';
			$tres['maxsubmit'] = '0';
		}

		if (!$queue->started)
		{
			$tres['max_user_run'] = '0';
		}

		$unit = 'cores';
		$resource = $queue->resource;
		if ($facet = $resource->getFacet('allocation_unit'))
		{
			$unit = $facet->value;
		}

		$queue->nodecores = $queue->subresource->nodecores;
		//$queue->maxijobfactor = maxijobfactor
		//$queue->maxijobuserfactor = maxijobuserfactor
		$queue->system_queue = 0;
		$queue->admin_queue = 0;

		if ($queue->groupid == -1)
		{
			$queue->system_queue = 1;
			if (substr($queue->name, 0, 4) == 'rcac')
			{
				$queue->admin_queue = 1;
			}
			if ($queue->cluster == '')
			{
				$queue->cluster = $resource->rolename + '-a';
				if ($queue->name == 'standby')
				{
					$queue->cluster = $resource->rolename + '-standby';
				}
				if ($queue->name == 'long')
				{
					$queue->cluster = $resource->rolename + '-a';
				}
				if ($queue->name == 'highmem')
				{
					$queue->cluster = $resource->rolename + '-c';
				}
				if ($queue->name == 'partner')
				{
					$queue->cluster = $resource->rolename + '-partner';
				}
			}
		}
		else
		{
			if ($unit == 'gpus')
			{
				//$tres['gres/gpu'] = $queue->totalcores;
				$tres['total'][] = [
					'type' => 'gres',
					'name' => 'gpu',
					'count' => $queue->totalcores
				];
			}
			elseif ($unit == 'sus')
			{
				//$tres['gres/gpu'] = $queue->totalcores;
			}
			else
			{
				$tres['total'][] = [
					'type' => 'cpu',
					'name' => null,
					'count' => $queue->totalcores
				];
				//$tres['cpu'] = $queue->totalcores;
			}
		}
	
		return $tres;
	}

	/**
	 * Retrieve or create a Slurm Account
	 *
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @return array
	 */
	private function getOrCreateAccount(Client $client, array $config, Queue $queue)
	{
		$account = $this->getAccount($client, $config, $queue);

		if (!$account)
		{
			$this->setAccount($client, $config, $queue);
		}
	}

	/**
	 * Retrieve or create a Slurm Account
	 *
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @return array
	 */
	private function setAccount(Client $client, array $config, Queue $queue)
	{
		// No account exist. Let's create it.
		$tres = $this->toSlurm($queue);

		/*if ($queue->aclgroups)
		{
			$aclgroups = explode(',', $queue->aclgroups);
		}*/
		$name = ($queue->isSystem() ? $queue->name : $queue->nameWithSubcluster);

		// Gather the list of users (associations)
		$associations = array();
		foreach ($queue->users as $queueuser)
		{
			$data = array(
				'user'      => $queueuser->user->username,
				'account'   => $name,
				'cluster'   => $queue->resource->rolename,
				'max'       => [
					'tres' => $tres,
				]
			);

			if ($queue->cluster)
			{
				$data['partition'] = $queue->resource->rolename . '-' . $queue->cluster;
			}

			if ($queue->priority)
			{
				$data['priority'] = $queue->priority;
			}

			if ($queue->defaultwalltime)
			{
				$data['default']['per'] = [
					'account' => [
						'wall_clock' => $queue->defaultwalltime
					]
				];
			}

			if ($queue->maxwalltime)
			{
				$data['max']['per'] = [
					'account' => [
						'wall_clock' => $queue->maxwalltime
					]
				];
			}

			$associations[] = $data;
		}

		$body = array(
			'name'         => $name,
			'description'  => $name,
			'organization' => $queue->group->name,
			'associations' => $associations,
			'coordinators' => [],
			'flags'        => [],
		);

		$res = $client->request('POST', $config['url'] . '/accounts', [
			'json' => $body
		]);

		if ($res->getStatusCode() >= 400)
		{
			throw new \Exception('Slurm API: Failed to create account for queue "' . $queue->name . '"', $res->getStatusCode());
		}

		/*foreach ($queue->qos as $qos)
		{
			// No account exist. Let's create it.
			$res = $client->request('POST', $config['url'] . '/qos', [
				'json' => array(
					'name'         => $qos->name,
					'description'  => $qos->description,
					'preempt' => '',
					'limits' => '',
					'priority' => 1,
					'usage_factor' => 0.0,
					'usage_threshold' => 0.0,
					'flags'        => []
				)
			]);
		}*/

		$account = json_decode($res->getBody()->getContents());

		return $account;
	}

	/**
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @return bool
	 */
	private function deleteAccount(Client $client, array $config, Queue $queue)
	{
		$url = $config['url'] . '/account/' . $queue->name;

		$account = $this->getAccount($client, $config, $queue);

		if ($account)
		{
			foreach ($account->associations as $association)
			{
				$client->request('DELETE', $config['url'] . '/associations?account=' . $association->account . '&user=' . $association->user . '&cluster=' . $association->cluster . '&partition=' . $association->partition);
			}

			$res = $client->request('DELETE', $url);

			if ($res->getStatusCode() >= 400)
			{
				throw new \Exception('Slurm API: Failed to delete account for queue "' . $queue->name . '"', $res->getStatusCode());
			}
		}

		return true;
	}

	/**
	 * Retrieve a Slurm Account
	 *
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @param  QueueUser $user
	 * @return array
	 */
	private function getAssociation(Client $client, array $config, Queue $queue, User $user)
	{
		$name = ($queue->isSystem() ? $queue->name : $queue->nameWithSubcluster);

		$res = $client->request('GET', $config['url'] . '/association?account=' . $name . '&user=' . $user->username . '&cluster=' . $queue->resource->rolename . '&partition=' . $queue->cluster);

		if ($res->getStatusCode() == 404)
		{
			return null;
		}
		elseif ($res->getStatusCode() >= 400)
		{
			throw new \Exception('Slurm API: Failed to retrieve association for queue "' . $queue->name . '", user "' . $user->username . '"', $res->getStatusCode());
		}

		return json_decode($res->getBody()->getContents());
	}

	/**
	 * Retrieve or create a Slurm Account
	 *
	 * @param  Client $client
	 * @param  array $config
	 * @param  Queue $queue
	 * @param  QueueUser $user
	 * @return array
	 */
	private function getOrCreateAssociation(Client $client, array $config, Queue $queue, User $user)
	{
		$association = $this->getAssociation($client, $config, $queue, $user);

		if (!$association)
		{
			$tres = $this->toSlurm($queue);

			$data = array(
				'user'      => $user->username,
				'account'   => ($queue->isSystem() ? $queue->name : $queue->nameWithSubcluster),
				'cluster'   => $queue->resource->rolename,
				//'partition' => $queue->cluster,
				'max'       => [
					'tres' => $tres,
				]
			);

			if ($queue->cluster)
			{
				$data['partition'] = $queue->cluster;
			}

			if ($queue->priority)
			{
				$data['priority'] = $queue->priority;
			}

			$res = $client->request('POST', $config['url'] . '/associations', [
				'json' => $data
			]);

			if ($res->getStatusCode() >= 400)
			{
				throw new \Exception('Slurm API: Failed to create association for queue "' . $queue->name . '"', $res->getStatusCode());
			}

			$association = json_decode($res->getBody()->getContents());
		}
		elseif ($res->getStatusCode() >= 400)
		{
			throw new \Exception('Slurm API: Failed to retrieve association for queue "' . $queue->name . '"', $res->getStatusCode());
		}

		return $association;
	}

	/**
	 * Handle creation of a queue (account) in Slurm
	 *
	 * @param   QueueCreated   $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event)
	{
		$queue = $event->queue;
		$config = $this->canProcessQueue($queue);

		if (!$config)
		{
			return;
		}

		try
		{
			$client = $this->getClient($config);
			$body = $this->setAccount($client, $config, $queue);
			$status = 201;
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('slurm', __METHOD__, 'POST', $status, $body, $config['url'] . '/accounts', $queue->id);
	}

	/**
	 * Handle clean-up in Rancher when a queue is deleted
	 *
	 * @param   QueueDeleted   $event
	 * @return  void
	 */
	public function handleQueueDeleted(QueueDeleted $event)
	{
		$queue = $event->queue;
		$config = $this->canProcessQueue($queue);

		if (!$config)
		{
			return;
		}

		$url = $config['url'] . '/account/' . ($queue->isSystem() ? $queue->name : $queue->nameWithSubcluster);

		try
		{
			$client = $this->getClient($config);

			$this->deleteAccount($client, $config, $queue);
			$status = 204;
			$body = [];
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('slurm', __METHOD__, 'DELETE', $status, $body, $url, $queue->id);
	}

	/**
	 * Add the given user to associated Rancher project
	 *
	 * @param   QosCreated|QosUpdated  $event
	 * @return  void
	 */
	public function handleQos($event)
	{
		$config = config('listener.slurm', []);

		if (empty($config))
		{
			return;
		}

		$config['url'] = $config['domain'] . ($config['port'] ? ':' . $config['port'] : '') . '/slurmdb/' . $config['version'] . '/qos';

		try
		{
			$client = $this->getClient($config);

			// Check if a qos already exists
			$qos = $event->qos;

			$tres = explode(',', $qos->grp_tres);
			foreach ($tres as $i => $tr)
			{
				if (!$tr)
				{
					continue;
				}
				$bits = explode('=', $tr);
				$name = null;
				if (strstr($bits[0], '/'))
				{
					$bots = explode('/', $bits[0]);
					$bits[0] = $bots[0];
					$name = $bots[1];
				}
				$tres[$i] = [
					'type'  => $bits[0],
					'name'  => $name,
					'count' => (int)$bits[1]
				];
			}

			$body = array(
				'name' => $qos->name,
				'description' => $qos->description,
				'priority' => $qos->priority,
				'usage_factor' => $qos->usage_factor ? $qos->usage_factor : 1.000000,
				'usage_threshold' => $qos->usage_thres,
				'flags' => [],
				'preempt' => [
					'list' => $qos->preemptList, //a list of other QOS's that it can preempt
					'mode' => $qos->preemptModeList,
					'exempt_time' => ($qos->preempt_exempt_time ? $qos->preempt_exempt_time : '-1'),
				],
				'limits' => [
					'grace_time' => ($qos->grace_time ? $qos->grace_time : '-1'),
					'max' => [
						'active_jobs' => [
							'accruing' => null,
							'count' => $qos->grp_jobs,
						],
						'tres' => [
							'total' => $tres,
							'minutes' => [
								'per' => [
									'qos' => ($qos->grp_tres_run_mins ? [$qos->grp_tres_run_mins] : []),
									'job' => ($qos->max_tres_mins_pj ? [$qos->max_tres_mins_pj] : []),
									'account' => ($qos->max_tres_run_mins_pa ? [$qos->max_tres_run_mins_pa] : []),
									'user' => ($qos->max_tres_run_mins_pu ? [$qos->max_tres_run_mins_pu] : []),
								]
							],
							'per' => [
								'account' => ($qos->max_tres_pa ? [$qos->max_tres_pa] : []),
								'job' => ($qos->max_tres_pj ? [$qos->max_tres_pj] : []),
								'node' => ($qos->max_tres_pn ? [$qos->max_tres_pn] : []),
								'user' => ($qos->max_tres_pu ? [$qos->max_tres_pu] : []),
							]
						],
						'wall_clock' => [
							'per' => [
								'qos' => ($qos->grp_wall ? $qos->grp_wall : '-1'),
								'job' => ($qos->max_wall_duration_per_job ? $qos->max_wall_duration_per_job : '-1'),
							]
						],
						'jobs' => [
							'active_jobs' => [
								'per' => [
									'account' => ($qos->max_submit_jobs_pa ? $qos->max_submit_jobs_pa : '-1'),
									'user' => ($qos->max_submit_jobs_per_user ? $qos->max_submit_jobs_per_user : '-1'),
								]
							],
							'per' => [
								'account' => ($qos->max_jobs_pa ? $qos->max_jobs_pa : '-1'),
								'submitted' => ($qos->grp_submit_jobs ? $qos->grp_submit_jobs : '-1'),
								'user' => ($qos->max_jobs_per_user ? $qos->max_jobs_per_user : '-1'),
							]
						],
						'accruing' => [
							'per' => [
								'account' => ($qos->max_jobs_accrue_pa ? $qos->max_jobs_accrue_pa : '-1'),
								'job' => ($qos->max_jobs_accrue_pj ? $qos->max_jobs_accrue_pj : '-1'),
								'user' => ($qos->max_jobs_accrue_pu ? $qos->max_jobs_accrue_pu : '-1'),
							]
						],
					],
					'factor' => $qos->limit_factor,
					'min' => [
						'priority_threshold' => ($qos->min_prio_thresh ? $qos->min_prio_thresh : '-1'),
						'tres' => [
							'per' => [
								'job' => ($qos->min_tres_pj ? [$qos->min_tres_pj] : [])
							]
						]
					]
				]
			);

			$res = $client->request('POST', $config['url'], [
				'json' => ['QOS' => [$body]]
			]);
			$status = $res->getStatusCode();

			if ($status >= 400)
			{
				throw new \Exception('Slurm API: Failed to create/update QoS for queue "' . $queue->name . '"', $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('slurm', __METHOD__, 'POST', $status, $body, $config['url']);
	}

	/**
	 * Add the given user to associated Rancher project
	 *
	 * @param   QosCreated  $event
	 * @return  void
	 */
	public function handleQosDeleted(QosDeleted $event)
	{
		$config = config('listener.slurm', []);

		if (empty($config))
		{
			return;
		}

		$qos = $event->qos;
		$config['url'] = $config['domain'] . ($config['port'] ? ':' . $config['port'] : '') . '/slurmdb/' . $config['version'] . '/qos/' . $qos->name;

		try
		{
			$client = $this->getClient($config);

			$res = $client->request('DELETE', $url);
			$status = $res->getStatusCode();
			if ($status >= 400)
			{
				throw new \Exception('Slurm API: Failed to delete QoS "' . $qos->name . '"', $status);
			}
			$body = [];
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('slurm', __METHOD__, 'DELETE', $status, $body, $url);
	}

	/**
	 * Add the given user to associated Rancher project
	 *
	 * @param   QueueUserCreated  $event
	 * @return  void
	 */
	public function handleQueueUserCreated(QueueUserCreated $event)
	{
		$queue = $event->user->queue;
		$config = $this->canProcessQueue($queue);

		if (!$config)
		{
			return;
		}

		try
		{
			$client = $this->getClient($config);

			// Check that an account even exists
			$account = $this->getAccount($client, $config, $queue);

			if (!$account)
			{
				return;
			}

			$user = $event->user->user;

			$found = false;
			foreach ($account->associations as $assoc)
			{
				if ($assoc->user == $user->username)
				{
					$found = true;
					break;
				}
			}

			if (!$found)
			{
				$slurmUser = $this->getOrCreateAssociation($client, $config, $queue, $user);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();

			$this->log('slurm', __METHOD__, 'POST', $status, $body, $config['url'], $event->user->userid);
		}
	}

	/**
	 * Remove the given user from the associated account
	 *
	 * @param   QueueUserDeleted  $event
	 * @return  void
	 */
	public function handleQueueUserDeleted(QueueUserDeleted $event)
	{
		$queue = $event->user->queue;
		$config = $this->canProcessQueue($queue);

		if (!$config)
		{
			return;
		}

		$body = '';
		$user = $event->user->user;
		$url = $config['url'] . '/association?user=' . $user->username . '&account=' . $queue->nameWithSubcluster . '&cluster=' . $queue->resource->rolename . '&partition=' . $queue->cluster;

		try
		{
			$client = $this->getClient($config);

			// Check that an account exist
			$account = $this->getAccount($client, $config, $queue);

			if (!$account)
			{
				return;
			}

			$found = false;
			foreach ($account->associations as $assoc)
			{
				if ($assoc->user == $user->username)
				{
					$found = true;
					break;
				}
			}

			if ($found)
			{
				// Remove the association
				$res = $client->request('DELETE', $url);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('slurm', __METHOD__, 'DELETE', $status, $body, $url, $event->user->userid);
	}

	/**
	 * Update resource limits
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleQueueAllocation($event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		if ($event instanceof QueueSizeCreated
		 || $event instanceof QueueSizeUpdated
		 || $event instanceof QueueSizeDeleted)
		{
			$queue = $event->size->queue;
		}
		elseif ($event instanceof QueueLoanCreated
		 || $event instanceof QueueLoanUpdated
		 || $event instanceof QueueLoanDeleted)
		{
			$queue = $event->loan->queue;
		}

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		$body = [];
		$url = '';

		try
		{
			$group = $queue->group;
			$client = $this->getClient($config);

			// Make sure the account has an allocation
			if ($queue->total)
			{
				// Make sure we have a Slurm account
				$account = $this->getAccount($client, $config, $queue);

				// Update the settings
				$this->setAccount($client, $config, $account, $queue);
			}
			else
			{
				// No allocation == no account
				$this->deleteAccount($client, $config, $queue);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();

			//$this->log('slurm', __METHOD__, 'PUT', $status, $body, $url);
		}
	}

	/**
	 * Call endpoint on cluster admin to pull in the latest Slurm config
	 *
	 * @param   Schedule  $event
	 * @return  void
	 */
	public function handleSchedule(Schedule $event)
	{
		$resource = $event->resource;
		$client = new Client();

		foreach ($resource->subresources as $subresource)
		{
			$schedulers = Scheduler::query()
				->where('queuesubresourceid', '=', $subresource->id)
				->get();

			if (!count($schedulers))
			{
				continue;
			}

			foreach ($schedulers as $scheduler)
			{
				$url = 'http://' . $scheduler->hostname . ':81';

				if ($event->isVerbose())
				{
					$event->command->comment('Calling ' . $url);
				}

				$res = $client->request('POST', $url, [
					'json' => ['slurm' => 'reconfig']
				]);
				$status = $res->getStatusCode();

				if ($status >= 400 && $event->isVerbose())
				{
					$event->command->error('Scheduler returned error when asked to pull Slurm config');
				}
			}
		}
	}
}
