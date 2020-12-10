<?php

namespace App\Listeners\Users\RoleProvision;

use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Resources\Events\ResourceMemberDeleted;
use App\Modules\History\Traits\Loggable;
use GuzzleHttp\Client;

/**
 * Role Provision listener
 */
class RoleProvision
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
		$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
		$events->listen(ResourceMemberStatus::class, self::class . '@handleResourceMemberStatus');
		$events->listen(ResourceMemberDeleted::class, self::class . '@handleResourceMemberDeleted');
	}

	/**
	 * Search for users
	 *
	 * @param   ResourceMemberCreated  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$url = $config['url'] . 'createOrUpdateRole';

		// Make call to resourcemember to generate role
		$body = array(
			'createOrUpdateRoleRequest' => array(
				'organization'   => 'rcs',
				'role'           => $event->resource->rolename,
				'requestorLogin' => auth()->user()->username,
				'customerLogin'  => $event->user->username,
				'primaryGroup'   => $event->user->primarygroup,
				'loginShell'     => $event->user->loginshell,
				'quota'          => '1',
				'piLogin'        => $event->user->pilogin
			)
		);

		try
		{
			$client = new Client();

			$res = $client->request('POST', $config['url'] . $url, [
				'auth' => [
					$config['user'],
					$config['password']
				],
				'body' => $body,
				'json' => ['body' => $body]
			]);

			$status = $res->getStatusCode();
			$body   = $res->getBody();
		}
		catch (\Exception $e)
		{
			//Log::error($e->getMessage());
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('roleprovision', __METHOD__, 'POST', $status, $body, $url);
	}

	/**
	 * Search for users
	 *
	 * @param   ResourceMemberDeleted  $event
	 * @return  void
	 */
	public function handleResourceMemberDeleted(ResourceMemberDeleted $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$url = $config['url'] . 'removeRole/rcs/' . $event->resource->rolename . '/' . auth()->user()->username . '/' . $event->user->username;

		try
		{
			$client = new Client();

			$body = $event->resource->rolename;

			$res = $client->request('DELETE', $config['url'] . $url, [
				'auth' => [
					$config['user'],
					$config['password']
				],
				'body' => $body,
				'json' => ['body' => $body]
			]);

			$status = $res->getStatusCode();
			$body   = $res->getBody();
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('rolerovision', __METHOD__, 'DELETE', $status, $body, $url);
	}

	/**
	 * Get status for a user
	 *
	 * @param   ResourceMemberStatus   $event
	 * @return  void
	 */
	public function handleResourceMemberStatus(ResourceMemberStatus $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$url = $config['url'] . 'getRoleStatus/rcs/' . $event->resource->rolename . '/' . $event->user->username;

		try
		{
			$client = new Client();

			$res = $client->request('GET', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status  = $res->getStatusCode();
			$results = $res->getBody();

			if ($status >= 400)
			{
				throw new \Exception(__METHOD__ . '(): Failed to find user ' . $event->resource->id . '.' . $event->user->id, $status);
			}

			if (!isset($results->roleStatus))
			{
				if (preg_match('/no record/', $results))
				{
					// catch error message for invalid user
					$status = 0;
				}
				else
				{
					// couldn't connect or something equally bad
					$status = -1;
				}
			}
			elseif ($results->roleStatus == 'NO_ROLE_EXISTS')
			{
				$status = 1;
			}
			elseif ($results->roleStatus == 'ROLE_ACCOUNT_CREATION_PENDING')
			{
				$status = 2;
			}
			elseif ($results->roleStatus == 'ROLE_ACCOUNTS_READY')
			{
				$status = 3;
			}
			elseif ($results->roleStatus == 'ROLE_REMOVAL_PENDING')
			{
				$status = 4;
			}
			else
			{
				$status = -1;
			}

			$event->status = $status;
		}
		catch (\Exception $e)
		{
			$status  = 500;
			$results = ['error' => $e->getMessage()];
		}

		$this->log('rolerovision', __METHOD__, 'GET', $status, $results, $url);
	}

	/**
	 * Plugin that loads module positions within content
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

		if (!$queue)
		{
			return;
		}

		// Create roles as necessary
		if (!$queue->scheduler->resource
		 || !$queue->scheduler->resource->rolename)
		{
			return;
		}

		$client = new Client();
		$url = $config['url'] . 'createOrUpdateRole';

		foreach ($queue->group->managers as $user)
		{
			$this->handleResourceMemberStatus($resourcemember = new ResourceMemberStatus($user, $queue->scheduler->resource));

			if ($resourcemember->status <= 0)
			{
				throw new \Exception(__METHOD__ . '(): Bad status for `resourcemember` ' . $user->id);
			}
			elseif ($resourcemember->status == 1 || $resourcemember->status == 4)
			{
				// Make call to resourcemember to generate role
				$body = array(
					'createOrUpdateRoleRequest' => array(
						'organization'   => 'rcs',
						'role'           => $queue->scheduler->resource->rolename,
						'requestorLogin' => auth()->user()->username,
						'customerLogin'  => $user->username,
						'primaryGroup'   => $resourcemember->primarygroup,
						'loginShell'     => $resourcemember->loginshell,
						'quota'          => '1',
						'piLogin'        => $resourcemember->pilogin
					)
				);

				$res = $client->request('POST', $url, [
					'auth' => [
						$config['user'],
						$config['password']
					],
					'body' => $body,
					'json' => ['body' => $body]
				]);

				$status = $res->getStatusCode();

				if ($status >= 400)
				{
					throw new \Exception(__METHOD__ . '(): Failed to create `resourcemember` entry for ' . $user->id);
				}

				$this->log('rolerovision', __METHOD__, 'POST', $status, $res, $url);
			}
		}
	}

	/**
	 * Get config values for listener
	 *
	 * @return  array
	 */
	private function config()
	{
		return config('listener.roleprovision', []);
	}
}
