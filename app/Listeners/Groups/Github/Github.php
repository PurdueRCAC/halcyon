<?php
namespace App\Listeners\Groups\Github;

use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\UnixGroup;
use App\Modules\Groups\Events\GroupUpdated;
use GuzzleHttp\Client;

/**
 * Group listener for Github
 */
class Github
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(GroupUpdated::class, self::class . '@handleGroupUpdated');
	}

	/**
	 * Create new github organization
	 *
	 * @param   GroupUpdated  $event
	 * @return  void
	 */
	public function handleGroupUpdated(GroupUpdated $event)
	{
		$group = $event->group;

		if (!$group->githuborgname)
		{
			return;
		}

		// Get usernames of owner to init organization
		$managers = $group->managers;

		if (count($managers) == 0)
		{
			return;
		}

		$config = config('listener.groups.github', []);

		if (empty($config))
		{
			return;
		}

		// Strip the name before checking database
		$orgname = preg_replace('/[^[:alnum:]]/', '', $group->name);

		// If the name is less than 8 chars, make it longer
		if (strlen($orgname) < 8)
		{
			if (strpos($orgname, 'group') === false
			 || strpos($orgname, 'Group' === false))
			{
				$orgname .= 'Organization';
			}
			elseif (strpos($orgname, 'group') !== false)
			{
				str_replace('group', 'organization', $orgname);
			}
			elseif (strpos($orgname, 'Group') !== false)
			{
				str_replace('Group', 'Organization', $orgname);
			}
		}

		//$group->githuborgname = $orgname;

		/*$exist = Group::query()
			->where('githuborgname', '=', $orgname)
			->get()
			->first();

		if ($exist && $exist->id)
		{
			return;
		}*/

		$unix = UnixGroup::query()
			->where('groupid', '=', $group->id)
			->where('shortname', 'like', 'rcs%0')
			->where(function($where)
			{
				$where->whereNull()
					->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->get()
			->first();

		if (!$unix)
		{
			return;
		}

		$base_unix = $unix->longname;
		$url = $config['url'];

		// Set up client
		$client = new Client([
			'auth' => [
				$config['user'],
				$config['password']
			]
		]);

		// Must initially populate with API user as admin so we can populate people. Dumb API.
		$return  = $client->post(
			$url . '/admin/organizations',
			array(
				'admin'        => $config['user'],
				'login'        => $orgname,
				'profile_name' => $group->name,
			)
		);

		if ($result->getStatusCode() >= 400)
		{
			return;
		}

		// Promote all owners to admin
		foreach ($managers as $row)
		{
			$result = $client->put(
				$url . '/orgs/' . $orgname . '/memberships/' . $row->user->username,
				['role' => 'admin']
			);

			if ($result->getStatusCode() > 400)
			{
				return;
			}
		}

		// Now create a base team
		$result = $client->post(
			$url . '/orgs/' . $orgname . '/teams',
			array(
				'name' => 'base',
				'description' => 'This base team is automatically populated with all members of this research group. It may be used by repos or may be left alone, but must be left in place for new group members to be automatically populated in this organization.'
			)
		);

		if ($result->getStatusCode() > 400)
		{
			return;
		}

		$result = json_decode($result->getBody());
		$team_id = $result->id;

		// Set ldap mapping for team for automatic sync.
		$result = $client->patch(
			$url . '/admin/ldap/teams/' . $team_id . '/mapping',
			array(
				'ldap_dn' => 'cn=rcac-' . $base_unix . ',ou=Group,dc=rcac,dc=purdue,dc=edu'
			)
		);

		if ($result->getStatusCode() > 400)
		{
			return;
		}

		// Schedule a sync of the team members
		$result = $client->post(
			$url . '/admin/ldap/teams/' . $team_id . '/sync',
			array()
		);

		if ($result->getStatusCode() > 400)
		{
			return;
		}

		// Now delete API user. Because dumb.
		$result = $client->delete(
			$url . '/orgs/' . $orgname . '/memberships/' . $config['user']
		);
	}
}
