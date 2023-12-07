<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Orders\Models\NoticeStatus;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Account;
use App\Modules\Orders\Mail\PendingApproval;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

class RemindApproversCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orders:remind {--debug : Output list of who to remind without emialing them}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Remind account approvers of orders waiting for them';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Finding orders needing approval...');
		}

		$interval = config('module.orders.remind_approvers_after', '1 week');

		if (!$interval)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->error('No time interval specified.');
			}
			return;
		}

		$dt = Carbon::now()->modify('-' . $interval);

		$orders = Order::query()
			->whereIn('notice', [
				NoticeStatus::PENDING_APPROVAL,
				NoticeStatus::PENDING_FULFILLMENT,
				NoticeStatus::PENDING_COLLECTION,
				NoticeStatus::COMPLETE
			])
			->where(function ($where) use ($dt)
			{
				$where->whereNull('datetimenotified')
					->where('datetimecreated', '<', $dt->toDateTimeString());
			})
			->orWhere('datetimenotified', '<', $dt->toDateTimeString())
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('Order #' . $order->id);
			}

			$approvers = array();

			foreach ($order->accounts as $account)
			{
				if ($account->isApproved() || $account->isDenied())
				{
					continue;
				}

				if ($account->approveruserid
				 && !in_array($account->approveruserid, $approvers))
				{
					array_push($approvers, $account->approveruserid);
				}
			}

			$emailed = array();

			// Send email to each subscriber
			$approvers = array_unique($approvers);

			if (!count($approvers))
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->comment("    No approvers needing notified.");
				}
				continue;
			}

			foreach ($approvers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || $user->trashed())
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("    User account not found for ID #{$subscriber}.");
					}
					continue;
				}

				// Prepare and send actual email
				$message = new PendingApproval($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("    Emailed pending payment approval order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("    Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			$order->datetimenotified = Carbon::now();
			$order->saveQuietly();
		}
	}
}
