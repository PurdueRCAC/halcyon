<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\RoleProvision;

//use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\History\Models\Log;
use GuzzleHttp\Client;

/**
 * Resource listener
 */
class RoleProvision
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		//$events->listen(ResourceMemberCreated::class, self::class . '@handleResourceMemberCreated');
	}

	/**
	 * Search for users
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleResourceMemberCreated(ResourceMemberCreated $event)
	{
		//$config = parse_ini_string_m(file_get_contents(conf_file('roleprovision')));
		$config = config('listener.roleprovision', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$client = new Client();

			$res = $client->request('GET', $config['url'], [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

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

		Log::create([
			'ip'              => request()->ip(),
			'user'            => auth()->user()->id,
			'status'          => $status,
			'transportmethod' => 'GET',
			'servername'      => request()->getHttpHost(),
			'uri'             => $config['url'] . $url,
			'app'             => 'role',
			'payload'         => json_encode($body),
		]);
	}

	/**
	 * Search for users
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleResourceMemberDeleted(ResourceMemberDeleted $event)
	{
		//$config = parse_ini_string_m(file_get_contents(conf_file('roleprovision')));
		$config = config('listener.roleprovision', []);

		if (empty($config))
		{
			return;
		}

		try
		{
			$client = new Client();

			$res = $client->request('GET', $config['url'], [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

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

		Log::create([
			'ip'              => request()->ip(),
			'user'            => auth()->user()->id,
			'status'          => $status,
			'transportmethod' => 'GET',
			'servername'      => request()->getHttpHost(),
			'uri'             => $config['url'] . $url,
			'app'             => 'role',
			'payload'         => json_encode($body),
		]);
	}

	/**
	 * Get status for a user
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleResourceMemberStatus(ResourceMemberStatus $event)
	{
		$config = config('listener.roleprovision', []);

		if (empty($config))
		{
			return;
		}

		$url = 'getRoleStatus/rcs/' . $event->resource->rolename . '/' . $event->user->username;

		try
		{
			$client = new Client();

			$res = $client->request('GET', $config['url'] . $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
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
			$status  = $e->getStatus();
			$results = ['error' => $e->getMessage()];
		}

		Log::create([
			'ip'              => request()->ip(),
			'user'            => auth()->user()->id,
			'status'          => $status,
			'transportmethod' => 'GET',
			'servername'      => request()->getHttpHost(),
			'uri'             => $config['url'] . $url,
			'app'             => 'role',
			'payload'         => json_encode($results),
		]);
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handleQueueCreated(QueueCreated $event)
	{
		$config = config('listener.roleprovision', []);

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

				$res = $client->request('POST', $config['url'] . 'createOrUpdateRole', [
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
			}
		}
	}
}
