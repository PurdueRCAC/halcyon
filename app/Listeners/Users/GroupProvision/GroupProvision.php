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
		$events->listen(UserUpdated::class, self::class . '@handleUserUpdated');
		$events->listen(UnixGroupCreating::class, self::class . '@handleUnixGroupCreating');
		$events->listen(UnixGroupDeleted::class, self::class . '@handleUnixGroupDeleted');
		$events->listen(UnixGroupMemberCreated::class, self::class . '@handleUnixGroupMemberCreated');
		$events->listen(UnixGroupMemberDeleted::class, self::class . '@handleUnixGroupMemberDeleted');
	}

	/**
	 * Should this plugin handle this unix group?
	 *
	 * @param   object  $unixgroup
	 * @return  bool
	 */
	private function shouldHandleUnixgroup($unixgroup)
	{
		if (substr($unixgroup->longname, 0, 2) == 'x-')
		{
			return false;
		}

		return true;
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

		if (!$this->shouldHandleUnixgroup($event->unixgroup))
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

					if (!$event->unixgroup->unixgid && isset($results->groupId) && is_numeric($results->groupId))
					{
						$event->unixgroup->unixgid = $results->groupId;
					}
				}

				error_log('GroupProvision: Created AIMO ACMaint group ' . $event->unixgroup->shortname . ' (' . $event->unixgroup->longname . ')');
			}
			elseif ($status == 400 && stristr('already exists', $res->getBody()->getContents()))
			{
				// See if this information is provided elsewhere
				event($e = new UnixGroupFetch($event->unixgroup->shortname));

				if (!$event->unixgroup->unixgid && !empty($e->results))
				{
					if (isset($e->results[0]['gidNumber']))
					{
						$event->unixgroup->unixgid = intval($e->results[0]['gidNumber'][0]);
					}
					elseif (isset($e->results[0]['gidnumber']))
					{
						$event->unixgroup->unixgid = intval($e->results[0]['gidnumber'][0]);
					}
				}
			}
			else
			{
				throw new \Exception('GroupProvision: Failed to create AIMO ACMaint group ' . $event->unixgroup->shortname . ': ' . $res->getBody()->getContents(), $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			error_log($e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'POST', $status, $body, 'createGroup/rcs', 0, $event->unixgroup->groupid);
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

		if (!$this->shouldHandleUnixgroup($event->unixgroup))
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
				error_log('GroupProvision: Removed AIMO ACMaint group ' . $event->unixgroup->shortname . ' (' . $event->unixgroup->longname . ')');
			}
			else
			{
				throw new \Exception('GroupProvision: Failed to remove AIMO ACMaint group ' . $event->unixgroup->shortname, $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			error_log($e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'DELETE', $status, $body, 'deleteGroup/rcs/' . $event->unixgroup->shortname, 0, $event->unixgroup->groupid);
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

		if (!$this->shouldHandleUnixgroup($member->unixgroup))
		{
			return;
		}

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
				error_log('GroupProvision: Added AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username);
			}
			else
			{
				throw new \Exception('GroupProvision: Failed to add AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents(), $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			$event->member->error = $e->getMessage();

			error_log($e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'PUT', $status, $body, 'addGroupMember/rcs/pucc_rcd/' . $member->unixgroup->shortname . '/' . $member->user->username, $member->user->id, $member->unixgroup->groupid);
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

		if (!$this->shouldHandleUnixgroup($member->unixgroup))
		{
			return;
		}

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
				error_log('GroupProvision: Removed AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username);
			}
			else
			{
				throw new \Exception('GroupProvision: Failed to remove AIMO ACMaint member for ' . $member->unixgroup->shortname . '/' . $member->user->username . ': ' . $res->getBody()->getContents(), $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			error_log($e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'DELETE', $status, $body, 'removeGroupMember/rcs/pucc_rcd/' . $member->unixgroup->shortname . '/' . $member->user->username, $member->user->id, $member->unixgroup->groupid);
	}

	/**
	 * Handle a user being updated
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

		$user = $event->user;

		if (!$user->loginShell)
		{
			return;
		}

		try
		{
			// Call central accounting service to request status
			$client = new Client();

			$url = $config['url'] . 'changeShell/rcs/pucc_rcd/' . $user->username . '?loginShell=' . $user->loginShell;

			$res = $client->request('GET', $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $res->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());

			if ($status < 400)
			{
				error_log('GroupProvision: Changed login shell in AIMO ACMaint member for ' . $user->username);

				// changeShell/organization/hostGroup/memberLogin?loginShell=
				// this should only be needed for admin users, rcac_misc contains hosts only relevant to rcac staff
				if ($user->can('manage users'))
				{
					$url = $config['url'] . 'changeShell/rcs/pucc_misc/' . $user->username . '?loginShell=' . $user->loginShell;

					$res = $client->request('GET', $url, [
						'auth' => [
							$config['user'],
							$config['password']
						]
					]);
				}
			}
			else
			{
				throw new \Exception('GroupProvision: Failed to change login shell in AIMO ACMaint for ' . $user->username, $status);
			}
		}
		catch (\Exception $e)
		{
			$status = $e->getCode();
			$status = $status ?: 500;
			$body   = ['error' => $e->getMessage()];

			error_log($e->getMessage());
		}

		$this->log('groupprovision', __METHOD__, 'GET', $status, $body, 'changeShell/rcs/pucc_misc/' . $user->username . '?loginShell=' . $user->loginShell, $user->id);
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
