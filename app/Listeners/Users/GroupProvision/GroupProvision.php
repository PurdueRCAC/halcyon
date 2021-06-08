<?php
namespace App\Listeners\Users\GroupProvision;

use App\Modules\Users\Events\UserUpdated;
use App\Modules\Groups\Events\UnixGroupCreating;
use App\Modules\Groups\Events\UnixGroupDeleted;
use App\Modules\Groups\Events\UnixGroupMemberCreated;
use App\Modules\Groups\Events\UnixGroupMemberDeleted;
use App\Modules\Groups\Events\UnixGroupFetch;
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
		//$events->listen(UserUpdated::class, self::class . '@handleUserUpdated');
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
	public function handleUnixGroupCreating(UnixGroupCreating $event)
	{
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
					'groupName'  => $event->unixgroup->shortname,
					'lgroupName' => 'rcac-' . $event->unixgroup->longname
				)
			);

			$res = $client->request('POST', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				],
				//'form_params' => $body,
				'json' => $body //['body' => $body]
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

				error_log(__METHOD__ . '(): Created AIMO ACMaint group ' . $event->unixgroup->shortname . ': ' . $res->getBody()->getContents());
			}
			elseif ($status == 400 && stristr('already exists', $res->getBody()->getContents()))
			{
				// See if this information is provided elsewhere
				event($e = new UnixGroupFetch($event->unixgroup->shortname));

				if (!empty($e->results))
				{
					if (isset($e->results[0]['gidNumber']))
					{
						$this->unixgroup->unixgid = intval($e->results[0]['gidNumber'][0]);
					}
					elseif (isset($e->results[0]['gidnumber']))
					{
						$this->unixgroup->unixgid = intval($e->results[0]['gidnumber'][0]);
					}
				}
			}
			else
			{
				error_log(__METHOD__ . '(): Failed to create AIMO ACMaint group ' . $event->unixgroup->shortname . ': ' . $res->getBody()->getContents());
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];

			error_log(__METHOD__ . '(): Failed to create AIMO ACMaint group ' . $event->unixgroup->shortname . ': ' . $e->getMessage());
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
		$config = $this->config();

		if (empty($config))
		{
			return;
		}

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'deleteGroup/rcs/' . $event->unixgroup->shortname;

			$res = $client->request('DELETE', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());

			if ($status < 400)
			{
				error_log(__METHOD__ . '(): Removed AIMO ACMaint group ' . $member->unixgroup->shortname . ': ' . $res->getBody()->getContents());
			}
			else
			{
				error_log(__METHOD__ . '(): Failed to remove AIMO ACMaint group ' . $member->unixgroup->shortname . ': ' . $res->getBody()->getContents());
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];

			error_log(__METHOD__ . '(): Failed to remove AIMO ACMaint group ' . $member->unixgroup->shortname . ': ' . $e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'DELETE', $status, $body, $url);
	}

	/**
	 * Search for users
	 *
	 * @param   UserUpdated  $event
	 * @return  void
	 */
	/*public function handleUserUpdated(UserUpdated $event)
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
	}*/

	/**
	 * Handle a unix group being created
	 *
	 * @param   UnixGroupMemberCreated  $event
	 * @return  void
	 */
	public function handleUnixGroupMemberCreated(UnixGroupMemberCreated $event)
	{
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

			if ($status < 400)
			{
				error_log(__METHOD__ . '(): Added AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents());
			}
			else
			{
				error_log(__METHOD__ . '(): Failed to add AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents());
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];

			error_log(__METHOD__ . '(): Failed to add AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $e->getMessage());
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

			if ($status < 400)
			{
				error_log(__METHOD__ . '(): Removed AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents());
			}
			else
			{
				error_log(__METHOD__ . '(): Failed to remove AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents());
			}
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];

			error_log(__METHOD__ . '(): Failed to remove AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $e->getMessage());
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
