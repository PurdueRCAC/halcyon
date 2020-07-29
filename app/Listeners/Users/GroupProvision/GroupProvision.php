<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\GroupProvision

use App\Modules\Users\Events\UserUpdated;
use App\Modules\History\Models\Log;
use GuzzleHttp\Client;

/**
 * Resource listener
 */
class GroupProvision
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserUpdated::class, self::class . '@handleUserUpdated');
	}

	/**
	 * Handle a unix group being created
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUnixGroupCreating(UnixGroupCreating $event)
	{
	}

	/**
	 * Search for users
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleUserUpdated(UserUpdated $event)
	{
		//$config = parse_ini_string_m(file_get_contents(conf_file('groupprovision')));
		$config = config('listener.groupprovision', []);

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
}
