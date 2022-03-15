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
use App\Halcyon\Utility\Number;

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
		//$events->listen(QueueDeleted::class, self::class . '@handleQueueDeleted');
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

				if (!$rancherUser)
				{
					throw new \Exception('Failed to create Rancher user for ' . $user->username);
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
				$url = $mem->links->remove;

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

		//try
		//{
			$group = $queue->group;
			$client = new Client([
				'headers' => ['Authorization' => 'Bearer ' . $config['user'] . ':' . $config['password']],
			]);

			$project = $this->getProject($client, $config, $group);

			if (!$project)
			{
				return;
			}

			$this->updateProject($client, $config, $project, $queue);
		/*}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->errors[] = $e->getMessage();

			//$this->log('rancher', __METHOD__, 'PUT', $status, $body, $url);
		}*/
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

			$this->log('rancher', __METHOD__, 'GET', $status, $results, $url);
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
		$url = $config['url'] . 'projectroletemplatebindings';
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

		/* Example of returned data:
		{
			"annotations": { },
			"baseType": "projectRoleTemplateBinding",
			"created": "2022-03-14T18:51:42Z",
			"createdTS": 1647283902000,
			"creatorId": "u-fdsgwkepwm",
			"groupId": "",
			"groupPrincipalId": "",
			"id": "p-fkmmx:prtb-f2chw",
			"labels": {
			"cattle.io/creator": "norman"
			},
			"links": {
				"remove": "…/v3/projectRoleTemplateBindings/p-fkmmx:prtb-f2chw",
				"self": "…/v3/projectRoleTemplateBindings/p-fkmmx:prtb-f2chw",
				"update": "…/v3/projectRoleTemplateBindings/p-fkmmx:prtb-f2chw"
			},
			"name": "prtb-f2chw",
			"namespaceId": null,
			"projectId": "local:p-fkmmx",
			"roleTemplateId": "project-member",
			"type": "projectRoleTemplateBinding",
			"userId": "u-fdsgwkepwm",
			"userPrincipalId": "",
			"uuid": "eccb9a3d-9c33-4848-95f4-5f62f4e02faf"
		}
		*/
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
		$totalmemory = $queue->totalcores * Number::toBytes(config('listener.rancher.quota.memory_per_core', '4GB'));

		$body = array(
			'resourceQuota' => [
				'limitsCpu'      => ($queue->totalcores * 1000) . 'm',
				'limitsMemory'   => $totalmemory,
				//'requestsCpu'    => '1000m',
				//'requestsMemory' => '128Mi',
				'pods' => config('listener.rancher.quota.pod_limit_project', 200),
			],
			'containerDefaultResourceLimit' => [
				'limitsCpu'      => config('listener.rancher.quota.cpu_limit_container', '100mCPU'),
				'limitsMemory'   => config('listener.rancher.quota.memory_limit_container', '128Mi'),
			],
			'namespaceDefaultResourceQuota' => [
				'limitsCpu'      => round($queue->totalcores * config('listener.rancher.quota.cpu_limit_namespacce', 0.25)) . 'm',
				'limitsMemory'   => round($totalmemory * config('listener.rancher.quota.memory_limit_namespacce', 0.25)),
				//'requestsCpu'    => "1000m",
				//'requestsMemory' => "128Mi",
				'pods' => config('listener.rancher.quota.pod_limit_namespace', 50),
			],
			/*'namespaceDefaultResourceQuota' => [
				'limitsCpu'      => (config('listener.rancher.quota.cpu_limit_project', 1000) * config('listener.rancher.quota.cpu_limit_namespace', 0.25)) . 'm',
				'limitsMemory'   => (config('listener.rancher.quota.memory_limit_project', 128) * config('listener.rancher.quota.memory_limit_namespace', 0.25)) . 'Mi',
				'requestsCpu'    => "1000m",
				'requestsMemory' => "128Mi",
			],*/
		);

		$url = $project->links->update;

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
		$url = $config['url'] . '/users?name=' . $user->name;

		$res = $client->request('GET', $url);

		$status = $res->getStatusCode();
		$found = false;

		if ($status >= 400)
		{
			throw new \Exception('Rancher API: Failed to retrieve users', $res->getStatusCode());
		}

		$results = json_decode($res->getBody()->getContents());

		if (isset($results->data))
		{
			foreach ($results->data as $usr)
			{
				if ((isset($usr->username) && $usr->username == $user->username)
				|| (isset($usr->principalIds) && in_array('shibboleth_user://' . $user->username, $usr->principalIds)))
				{
					$found = $usr;
					break;
				}
			}
		}

		$this->log('rancher', __METHOD__, 'GET', $status, $results, $url, $user->id);

		return $found;
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
			'name' => $user->name,
			'state' => 'active',
			'enabled' => true,
			//'username' => $user->username, Don't set this for shibboleth accounts
			'mustChangePassword' => false,
			'principalIds' => [
				'shibboleth_user://' . $user->username,
			],
			'description' => 'Account created by RCAC portal for ' . $user->username,
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
