<?php

namespace App\Modules\Queues\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use App\Modules\Resources\Models\Asset;
use App\Modules\Resources\Events\ResourceMemberCreated;
use App\Modules\Resources\Events\ResourceMemberStatus;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use Carbon\Carbon;

class FixStatusCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'queues:fixstatus {--debug}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fix user status with external sources.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		$now = Carbon::now();

		$items = \App\Modules\History\Models\Log::query()
			->where('app', '=', 'roleprovision')
			->where('datetime', '>', $now->modify('-1 month')->toDateTimeString())
			->where('status', '=', 500)
			->where('transportmethod', '=', 'GET')
			->orderBy('id', 'asc')
			->get();

		if (!count($items))
		{
			$this->comment('No items with errors found in logs.');
			return;
		}

		foreach ($items as $log)
		{
			$parts = explode('/', $log->uri);
			$username = array_pop($parts);

			$user = User::findByUsername($username);

			if (!$user || !$user->id)
			{
				continue;
			}

			$this->comment('Username: ' . $u->id . ' (' . $u->username . ')');

			$resource = array_pop($parts);

			$asset = \App\Modules\Resources\Models\Asset::query()->where('rolename', '=', $resource)->first();

			 if (!$asset || !$asset->id)
			{
				$this->comment('Resource: ' . $resource . ' not found. Skipping...');
				continue;
			}

			$qu = (new \App\Modules\Queues\Models\User)->getTable();
			$q = (new \App\Modules\Queues\Models\Queue)->getTable();
			$s = (new \App\Modules\Resources\Models\Subresource)->getTable();
			$c = (new \App\Modules\Resources\Models\Child)->getTable();
			$a = (new \App\Modules\Resources\Models\Asset)->getTable();

			$total = \App\Modules\Queues\Models\User::query()
				->select($qu . '.*')
				->join($q, $q . '.id', $qu . '.queueid')
				->join($s, $s . '.id', $q . '.subresourceid')
				->join($c, $c . '.subresourceid', $s . '.id')
				->join($a, $a . '.id', $c . '.resourceid')
				->withTrashed()
				->whereIsActive()
				->whereNull($s . '.datetimeremoved')
				->whereNull($a . '.datetimeremoved')
				->where($a . '.id', '=', $asset->id)
				->where($qu . '.userid', '=', $user->id)
				->count();

			if (!$total)
			{
				$this->comment('No active queues found for user ' . $username . ' and resource ' . $resource . '. Skipping...');
				continue;
			}

			event($event = new \App\Modules\Resources\Events\ResourceMemberStatus($asset, $user));

			if ($event->status == 1)
			{
				event($event = new \App\Modules\Resources\Events\ResourceMemberCreated($asset, $user));

				$this->comment('Adding role: ' . $resource . ' to user #' . $user->id . ' (' . $user->username . ')');
			}
		}
	}
}
