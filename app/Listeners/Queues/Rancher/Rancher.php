<?php

namespace App\Listeners\Queues\Rancher;

use App\Modules\Queues\Events\QueueCreated;
use App\Modules\Queues\Events\UserCreated as QueueUserCreated;
use App\Modules\Queues\Events\UserDeleted as QueueUserDeleted;
use App\Modules\Queues\Events\QueueSizeCreated;
use App\Modules\Queues\Events\QueueSizeUpdated;
use App\Modules\Queues\Events\QueueSizeDeleted;
use App\Modules\Queues\Events\QueueLoanCreated;
use App\Modules\Queues\Events\QueueLoanUpdated;
use App\Modules\Queues\Events\QueueLoanDeleted;
use App\Modules\History\Traits\Loggable;
use GuzzleHttp\Client;

/**
 * Rancher listener
 */
class Rancher
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
		// Create/update/delete Rancher projects
		$events->listen(QueueCreated::class, self::class . '@handleQueueCreated');
		// Update Rancher project memberships
		$events->listen(QueueUserCreated::class, self::class . '@handleQueueUserCreated');
		$events->listen(QueueUserDeleted::class, self::class . '@handleQueueUserDeleted');
		// Update Rancher resource limits
		$events->listen(QueueSizeCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueSizeDeleted::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanCreated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanUpdated::class, self::class . '@handleQueueAllocation');
		$events->listen(QueueLoanDeleted::class, self::class . '@handleQueueAllocation');
	}

	/**
	 * Check if this listener should handle this Queue
	 *
	 * @param  Queue $queue
	 * @return bool
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

		$facet = $queue->scheduler->resource->getFacet('rancher');

		if (!$facet || !$facet->value)
		{
			return false;
		}

		return true;
	}

	/**
	 * Add the given resource as a role to each manager of the group that owns the newly created queue
	 *
	 * @param   QueueCreated   $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$queue = $event->queue;

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		try
		{
			$client = new Client([
				/*'auth' => [
					$config['user'],
					$config['password']
				],*/
				'headers' => ['Authorization' => 'Bearer ' . $config['user'] . ':' . $config['password']],
			]);

			// Check for a project representing the group
			$group = $queue->group;

			$project = $this->getProject($client, $config, $group);

			if (!$project)
			{
				$project = $this->createProject($client, $config, $group);
			}

			// Auto-add managers to projects
			foreach ($queue->group->managers as $manager)
			{
				$user = $manager->user;

				$rancherUser = $this->getUser($client, $config, $user);

				if (!$rancherUser)
				{
					$rancherUser = $this->createUser($client, $config, $user);
				}

				$member = $this->getProjectMember($client, $config, $project, $rancherUser);

				if (!$member)
				{
					$this->createProjectMember($client, $config, $project, $rancherUser);
				}
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();

			$this->log('rancher', __METHOD__, 'POST', $status, $body, $url);
		}
	}

	/**
	 * Add the given user to associated Rancher project
	 *
	 * @param   QueueUserCreated  $event
	 * @return  void
	 */
	public function handleQueueUserCreated(QueueUserCreated $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$queue = $event->user->queue;

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		$url = '';

		try
		{
			// Check for a project representing the group
			$group = $queue->group;
			$client = new Client([
				'headers' => ['Authorization' => 'Bearer ' . $config['user'] . ':' . $config['password']],
			]);

			$project = $this->getProject($client, $config, $group);

			if (!$project)
			{
				throw new \Exception('Rancher API: Failed to find project for group ' . $group->name, 404);
			}

			$user = $event->user->user;

			$rancherUser = $this->getUser($client, $config, $user);

			if (!$rancherUser)
			{
				$rancherUser = $this->createUser($client, $config, $user);
			}

			$member = $this->getProjectMember($client, $config, $project, $rancherUser);

			if (!$member)
			{
				$this->createProjectMember($client, $config, $project, $rancherUser);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();

			$this->log('rancher', __METHOD__, 'POST', $status, $body, $url, $event->user->userid);
		}
	}

	/**
	 * Remove the given user from the associated Rancher project
	 *
	 * @param   QueueUserDeleted  $event
	 * @return  void
	 */
	public function handleQueueUserDeleted(QueueUserDeleted $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$queue = $event->user->queue;

		if (!$this->canProcessQueue($queue))
		{
			return;
		}

		$body = [];
		$url = '';

		try
		{
			$group = $queue->group;
			$client = new Client([
				'headers' => ['Authorization' => 'Bearer ' . $config['user'] . ':' . $config['password']],
			]);

			$project = $this->getProject($client, $config, $group);

			if (!$project)
			{
				return;
			}

			$user = $event->user->user;

			$rancherUser = $this->getUser($client, $config, $user);

			if (!$rancherUser)
			{
				return;
			}

			if ($mem = $this->getProjectMember($client, $config, $project, $rancherUser))
			{
				$url = $mem->actions->delete;

				$res = $client->request('DELETE', $url);

				$status = $res->getStatusCode();

				if ($status >= 400)
				{
					throw new \Exception('Rancher API: Failed to delete project member entry for ' . $user->username);
				}
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('rancher', __METHOD__, 'DELETE', $status, $body, $url, $event->user->userid);
	}

	/**
	 * Update Rancher resource limits
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
			$client = new Client([
				'headers' => ['Authorization' => 'Bearer ' . $config['user'] . ':' . $config['password']],
			]);

			$project = $this->getProject($client, $config, $group);

			if (!$project)
			{
				return;
			}

			$this->updateProject($client, $config, $queue);
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();
		}

		$this->log('rancher', __METHOD__, 'DELETE', $status, $body, $url, $event->user->userid);
	}

	/*
	|--------------------------------------------------------------------------
	| API Interactions
	|--------------------------------------------------------------------------
	*/

	/**
	 * Retrieve a Rancher user account
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $user
	 * @return object
	 */
	private function getProject($client, $config, $group)
	{
		$uuid = 'group' . $group->id;
		$url = $config['url'] . 'projects?description=' . $uuid;

		$res = $client->request('GET', $url);

		$found = false;
		$status = $res->getStatusCode();

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to retrieve projects', $status);
		}

		$this->log('rancher', __METHOD__, 'GET', $status, ['description' => $group->id], $url);

		$results = json_decode($res->getBody()->getContents());

		if (isset($results->data))
		{
			foreach ($results->data as $project)
			{
				if ($project->description == $uuid)
				{
					$found = true;
					break;
				}
			}
		}

		if (!$found)
		{
			return false;
		}

		return $project;
	}

	/**
	 * Get a rancher project membership
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $project
	 * @param  object $member
	 * @return object
	 */
	private function getProjectMember($client, $config, $project, $member)
	{
		if (!isset($project->members))
		{
			$url = $project->links->projectRoleTemplateBindings;

			$res = $client->request('GET', $url);

			$status = $res->getStatusCode();

			if ($status >= 400)
			{
				throw new \Exception('Rancher API: Failed to retrieve project members', $status);
			}

			$results = json_decode($res->getBody()->getContents());

			if (isset($results->data))
			{
				$project->members = $results->data;
			}

			$this->log('rancher', __METHOD__, 'GET', $status, $body, $url);
		}

		foreach ($project->members as $mem)
		{
			if ($mem->userId == $member->id)
			{
				return $mem;
			}
		}

		return false;
	}

	/**
	 * Create a Rancher project membership
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $project
	 * @param  object $rancherUser
	 * @return object
	 */
	private function createProjectMember($client, $config, $project, $rancherUser)
	{
		$url = $config['url'] . '/projectroletemplatebindings';
		$body = [
			'projectId' => $project->id,
			'userId' => $rancherUser->id,
			'roleTemplateId' => 'project-member',
			/* v1.x
			'description'    => $user->username,
			'role'           => 'member', //member, owner, readonly, restricted
			'externalId'     => $user->id,
			'externalIdType' => 'halcyon_user',
			*/
		];

		$res = $client->request('POST', $url, [
			'json' => $body
		]);

		$status = $res->getStatusCode();

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to create project member entry for ' . $user->username);
		}

		$projectMember = json_decode($res->getBody()->getContents());

		$this->log('rancher', __METHOD__, 'POST', $status, $body, $url);

		return $projectMember;
	}

	/**
	 * Create a Rancher project based on a group
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $group
	 * @return object
	 */
	private function createProject($client, $config, $group)
	{
		$body = array(
			// Required
			'clusterId' => 'local',
			'name' => $group->name,
			// Optional
			'description' => 'group' . $group->id,
			//'projectTemplateId'      => '1pt5',
			//'uuid'                   => $uuid,
			//'hostRemoveDelaySeconds' => null,
			//'allowSystemRole'        => false,
			//'members'                => array(),
			//'virtualMachine'         => false,
			//'servicesPortRange'      => '65535,49153',
			//'projectLinks'           => array(),
			//'namespaceId' => null,
			//'resourceQuota' => null,
			//'enableProjectMonitoring' => false,
		);

		$url = $config['url'] . 'projects';

		$res = $client->request('POST', $url, [
			'json' => $body
		]);

		$status = $res->getStatusCode();

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to create project for ' . $group->name, $status);
		}

		$this->log('rancher', __METHOD__, 'POST', $status, $body, $url);

		$project = json_decode($res->getBody()->getContents());

		return $project;
	}

	/**
	 * Create a Rancher project based on a group
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $group
	 * @return object
	 */
	private function updateProject($client, $config, $project, $queue)
	{
		$body = array(
			'containerDefaultResourceLimit' => [
				'limitsCpu'      => $queue->totalcores . 'm',
				'limitsMemory'   => config('listener.rancher.quota.memory_limit_project', 128) . 'Mi',
				'requestsCpu'    => '1000m',
				'requestsMemory' => '128Mi',
			],
			/*'namespaceDefaultResourceQuota' => [
				'limitsCpu'      => (config('listener.rancher.quota.cpu_limit_project', 1000) * config('listener.rancher.quota.cpu_limit_namespace', 0.25)) . 'm',
				'limitsMemory'   => (config('listener.rancher.quota.memory_limit_project', 128) * config('listener.rancher.quota.memory_limit_namespace', 0.25)) . 'Mi',
				'requestsCpu'    => "1000m",
				'requestsMemory' => "128Mi",
			],*/
		);

		$url = $project->links->self;

		$res = $client->request('PUT', $url, [
			'json' => $body
		]);

		$status = $res->getStatusCode();

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to update project resource limits for queue ' . $queue->name, $status);
		}

		$this->log('rancher', __METHOD__, 'PUT', $status, $body, $url);

		$project = json_decode($res->getBody()->getContents());

		return $project;
	}

	/**
	 * Retrieve a Rancher user account
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $user
	 * @return object
	 */
	private function getUser($client, $config, $user)
	{
		$url = $config['url'] . '/users?username=' . $user->username;

		$res = $client->request('GET', $url);

		if ($res->getStatusCode() >= 400)
		{
			throw new \Exception('Rancher API: Failed to retrieve users', $res->getStatusCode());
		}

		$results = json_decode($res->getBody()->getContents());

		if (isset($results->data))
		{
			foreach ($results->data as $usr)
			{
				if ($usr->username == $user->username)
				{
					return $usr;
				}
			}
		}

		$this->log('rancher', __METHOD__, 'GET', $status, $body, $url, $user->id);

		return false;
	}

	/**
	 * Create a Rancher user account
	 *
	 * @param  object $client
	 * @param  array  $config
	 * @param  object $user
	 * @return object
	 */
	private function createUser($client, $config, $user)
	{
		$body = array(
			'name' => $user->username,
			'state' => 'active',
			'enabled' => true,
		);

		$url = $config['url'] . '/users';

		$res = $client->request('POST', $url, [
			'json' => $body
		]);

		$status = $res->getStatusCode();

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to create user entry for ' . $user->username);
		}

		$results = json_decode($res->getBody()->getContents());

		$this->log('rancher', __METHOD__, 'POST', $status, $body, $url, $user->id);

		return $results;
	}

	/**
	 * Get config values for listener
	 *
	 * @return  array
	 */
	private function config()
	{
		return config('listener.rancher', []);
	}
}
