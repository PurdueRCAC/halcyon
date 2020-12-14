<?php
namespace App\Listeners\Users\GroupProvision;

use App\Modules\Users\Events\UserUpdated;
use App\Modules\Groups\Events\UnixGroupCreating;
use App\Modules\Groups\Events\UnixGroupDeleted;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;
use App\Modules\History\Traits\Loggable;
use GuzzleHttp\Client;

/**
 * Group Provision listener
 */
class GroupProvision
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
		$events->listen(UserUpdated::class, self::class . '@handleUserUpdated');
		$events->listen(UnixGroupCreating::class, self::class . '@handleUnixGroupCreating');
		$events->listen(UnixGroupDeleted::class, self::class . '@handleUnixGroupDeleted');
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
	}

	/**
	 * Handle a unix group being created
	 *
	 * @param   UnixGroupCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupCreating(UnixGroupCreated $event)
	{
		return;
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'createGroup/rcs';
			$body = array(
				'provisionGroupServiceCreateGroupRequest' => array(
					'groupName'  => $this->unixgroup->shortname,
					'lgroupName' => 'rcac-' . $this->unixgroup->longname
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
			$body   = json_decode($res->getBody()->getContents());

			if ($status < 400)
			{
				if ($body)
				{
					$results = $body->provisionGroupServiceCreateGroupResponse;

					if (isset($results->groupId) && is_numeric($results->groupId))
					{
						$this->unixgroup->unixgid = $results->groupId;
					}
				}
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('groupprovision', __METHOD__, 'POST', $status, $body, $url);
	}

	/**
	 * Handle a unix group being deleted
	 *
	 * @param   UnixGroupDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupDeleted(UnixGroupDeleted $event)
	{
		return;
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'deleteGroup/rcs/' . $this->unixgroup->shortname;

			$res = $client->request('DELETE', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('groupprovision', __METHOD__, 'DELETE', $status, $body, $url);
	}

	/**
	 * Search for users
	 *
	 * @param   UserUpdated  $event
	 * @return  void
	 */
	public function handleUserUpdated(UserUpdated $event)
	{
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$url = $config['url'];

		try
		{
			$client = new Client();

			$res = $client->request('GET', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('groupprovision', __METHOD__, 'GET', $status, $body, $url);
	}

	/**
	 * Handle a unix group being created
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
		return;
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$member = $event->member;

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'addGroupMember/rcs/pucc_rcd/' . $member->unixgroup->shortname . '/' . $member->user->username;

			$res = $client->request('PUT', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('groupprovision', __METHOD__, 'POST', $status, $body, $url);
	}

	/**
	 * Handle a unix group being deleted
	 *
	 * @param   UnixGroupMemberDeleted  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberDeleted(UnixGroupMemberDeleted $event)
	{
		return;
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		$member = $event->member;

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'removeGroupMember/rcs/pucc_rcd/' . $member->unixgroup->shortname . '/' . $member->user->username;

			$res = $client->request('PUT', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		$this->log('groupprovision', __METHOD__, 'DELETE', $status, $body, $url);
	}

	/**
	 * Get config values for listener
	 *
	 * @return  array
	 */
	private function config()
	{
		return config('listener.groupprovision', []);
	}
}
