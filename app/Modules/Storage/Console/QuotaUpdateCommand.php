<?php

namespace App\Modules\Storage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Loan;
use App\Modules\Storage\Models\Purchase;
use App\Modules\Storage\Mail\Expiring;
use App\Modules\Messages\Models\Type as MessageType;
use Carbon\Carbon;


class QuotaUpdateCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'storage:quotaupdate
							{--debug : Do not perform actions, only report what will happen}
							{--noemail : Do not send warning emails}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check for expired storage loans and purchases and adjust quotas accordingly.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$debug = $this->option('debug') ? true : false;
		$noemail = $this->option('noemail') ? true : false;

		if (!$noemail)
		{
			if (!$this->emailExpiring($debug))
			{
				return Command::FAILURE;
			}
		}

		if (!$this->updateExpired($debug))
		{
			return Command::FAILURE;
		}

		return Command::SUCCESS;
	}

	/**
	 * Email group managers about expiring loans/purchases
	 */
	private function emailExpiring(bool $debug = false): bool
	{
		// Find all active directories with allocations that will expire in X timeperiod
		//
		// Send emails to the groups' managers

		$now  = Carbon::now()->modify('+6 days');
		$future = Carbon::now()->modify('+7 days');

		$loans = Loan::query()
			->withTrashed()
			->where('datetimestop', '>', $now->toDateTimeString())
			->where('datetimestop', '<=', $future->toDateTimeString())
			->where('groupid', '>', 0)
			->get();

		$purchases = Purchase::query()
			->withTrashed()
			->where('datetimestop', '>', $now->toDateTimeString())
			->where('datetimestop', '<=', $future->toDateTimeString())
			->where('groupid', '>', 0)
			->get();

		if (!count($loans) && !count($purchases))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No expiring allocations found.');
			}

			return true;
		}

		$storage = array();
		foreach ($loans as $loan)
		{
			if (!isset($storage[$loan->groupid]))
			{
				$storage[$loan->groupid] = array();
			}

			if (in_array($loan->resourceid, $storage[$loan->groupid]))
			{
				continue;
			}

			$storage[$loan->groupid][] = $loan->resourceid;
		}

		foreach ($purchases as $purchase)
		{
			if (!isset($storage[$purchase->groupid]))
			{
				$storage[$purchase->groupid] = array();
			}

			if (in_array($purchase->resourceid, $storage[$purchase->groupid]))
			{
				continue;
			}

			$storage[$purchase->groupid][] = $purchase->resourceid;
		}

		foreach ($storage as $groupid => $resources)
		{
			$dirs = Directory::query()
				->whereIn('resourceid', $resources)
				->where('groupid', '=', $groupid)
				->where('parentstoragedirid', '=', 0)
				->withTrashed()
				->get();

			if (!count($dirs))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Directories not found for groupid ' . $groupid . ' / resources: ' . implode(',', $resources));
				}
				continue;
			}

			$expiring = array();
			$group = null;
			foreach ($dirs as $dir)
			{
				if ($dir->trashed())
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->comment('Directory ' . $dir->name . ' for groupid ' . $groupid . ' / resourceid ' . $dir->resourceid . ' was removed');
					}
					continue;
				}

				if (!$dir->storageResource)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Storage resource not found for directory #' . $dir->id);
					}
					continue;
				}

				$expiring[] = $dir;

				if (empty($group))
				{
					$group = $dir->group;
				}
			}

			if (!$group)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Group not found for #' . $groupid);
				}
				continue;
			}

			foreach ($group->managers as $manager)
			{
				$user = $manager->user;

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("Email address not found for user {$user->name}.");
					}
					continue;
				}

				$message = new Expiring($expiring, $user, $group);
				$message->headers()->text([
					'X-Command' => 'storage:quotaupdate'
				]);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Emailed expiring storage loans/purchases for group ' . $group->name . ' to ' . $user->email);

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);
			}
		}

		return true;
	}

	/**
	 * Update quotas for directories with expired loans/purchases
	 */
	private function updateExpired(bool $debug = false): bool
	{
		// Find all active directories with allocations that expired in the past X timeperiod
		//
		// Submit a MQ to setquota
		// Update the datetimeconfigured field

		$now  = Carbon::now();
		$past = Carbon::now()->modify('-30 days');

		$loans = Loan::query()
			->withTrashed()
			->where('datetimestop', '>', $past->toDateTimeString())
			->where('datetimestop', '<=', $now->toDateTimeString())
			->where('groupid', '>', 0)
			->get();

		$purchases = Purchase::query()
			->withTrashed()
			->where('datetimestop', '>', $past->toDateTimeString())
			->where('datetimestop', '<=', $now->toDateTimeString())
			->where('groupid', '>', 0)
			->get();

		if (!count($loans) && !count($purchases))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No expired allocations found.');
			}

			return true;
		}

		$storage = array();
		foreach ($loans as $loan)
		{
			if (!isset($storage[$loan->groupid]))
			{
				$storage[$loan->groupid] = array();
			}

			if (in_array($loan->resourceid, $storage[$loan->groupid]))
			{
				continue;
			}

			$storage[$loan->groupid][] = $loan->resourceid;
		}

		foreach ($purchases as $purchase)
		{
			if (!isset($storage[$purchase->groupid]))
			{
				$storage[$purchase->groupid] = array();
			}

			if (in_array($purchase->resourceid, $storage[$purchase->groupid]))
			{
				continue;
			}

			$storage[$purchase->groupid][] = $purchase->resourceid;
		}

		foreach ($storage as $groupid => $resources)
		{
			$dirs = Directory::query()
				->whereIn('resourceid', $resources)
				->where('groupid', '=', $groupid)
				->where('parentstoragedirid', '=', 0)
				->withTrashed()
				->get();

			if (!count($dirs))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Directories not found for groupid ' . $groupid . ' / resources: ' . implode(',', $resources));
				}
				continue;
			}

			foreach ($dirs as $dir)
			{
				if ($dir->trashed())
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->comment('Directory ' . $dir->name . ' for groupid ' . $groupid . ' / resourceid ' . $dir->resourceid . ' was removed');
					}
					continue;
				}

				if (!$dir->storageResource)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Storage resource not found for directory #' . $dir->id);
					}
					continue;
				}

				// Get the current byte total
				$items = $dir->resourceTotal;
				if (!empty($items))
				{
					$last = end($items);
					$dir->bytes = $last['bytes'];
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info('Directory #' . $dir->id . ' being set to ' . $dir->formattedBytes);

					if ($debug)
					{
						continue;
					}
				}

				// Update the database entry as needed
				if ($dir->bytes != $dir->getOriginal('bytes'))
				{
					$dir->saveQuietly();
				}

				// Get the appropriate message for the message queue 
				// to update the quota and submit it
				$typeid = $dir->storageResource->createtypeid;

				if (!$typeid)
				{
					$type = MessageType::query()
						->where('resourceid', '=', $dir->resourceid)
						->where('name', 'like', 'fileset %')
						->first();

					if ($type)
					{
						$typeid = $type->id;
					}
				}

				if (!$typeid)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error('Message queue type for quota update not found for storage resource ' . $dir->storageResource->name);
					}
				}

				$dir->addMessageToQueue($typeid);
			}
		}

		return true;
	}
}
