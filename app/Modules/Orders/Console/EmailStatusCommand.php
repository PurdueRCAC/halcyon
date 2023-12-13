<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\NoticeStatus;
use App\Modules\Orders\Mail\PendingPayment;
use App\Modules\Orders\Mail\PendingAssignment;
use App\Modules\Orders\Mail\PendingApproval;
use App\Modules\Orders\Mail\PaymentDenied;
use App\Modules\Orders\Mail\PaymentApproved;
use App\Modules\Orders\Mail\Ticket;
use App\Modules\Orders\Mail\Fulfilled;
use App\Modules\Orders\Mail\Complete;
use App\Modules\Orders\Mail\Canceled;
use App\Modules\Orders\Events\OrderFulfilled;
use App\Modules\Users\Models\User;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

class EmailStatusCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orders:emailstatus {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email order status as it changes.';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle(): void
	{
		$debug = $this->option('debug') ? true : false;

		$roles = config('module.orders.staff', []);
		$admins = array();
		$processed = array();

		// Get admins
		if (!empty($roles))
		{
			$admins = Map::query()
				->whereIn('role_id', $roles)
				->get()
				->pluck('user_id')
				->toArray();
			$admins = array_unique($admins);
		}

		//--------------------------------------------------------------------------
		// STEP 1: Order Entered
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process new orders pending payment info...');
		}

		$orders = Order::query()
			->where('notice', '=', NoticeStatus::PENDING_PAYMENT)
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			$emailed = array();

			// Get the ordertype
			foreach ($order->items as $item)
			{
				if ($item->isRecurring() && !$item->isOriginal())
				{
					$order->type = 'renewal';
				}
				else
				{
					$order->type = 'new';
				}
			}

			$subscribers = $admins;
			$subscribers[] = $order->userid;
			$subscribers[] = $order->submitteruserid;
			$subscribers = array_unique($subscribers);

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new PendingPayment($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed new order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->offsetUnset('type');
			if ($order->total > 0)
			{
				$order->update(['notice' => NoticeStatus::PENDING_BOASSIGNMENT, 'datetimenotified' => Carbon::now()]);
			}
			else
			{
				// If the order total is zero, skip "pending payment info" and "pending approval"
				$order->update(['notice' => NoticeStatus::PENDING_COLLECTION, 'datetimenotified' => Carbon::now()]);
			}
			$processed[] = $order->id;
		}

		//--------------------------------------------------------------------------
		// STEP 2: Payment information entered
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process new orders pending business office assignment...');
		}

		$orders = Order::query()
			->where('notice', '=', NoticeStatus::PENDING_BOASSIGNMENT)
			->whereNotIn('id', $processed)
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(NoticeStatus::class . '::' . strtoupper($order->status)) < NoticeStatus::PENDING_BOASSIGNMENT)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('  Skipping order #' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			// Send email to each subscriber
			foreach ($admins as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new PendingAssignment($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed pending payment info order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => NoticeStatus::PENDING_APPROVAL, 'datetimenotified' => Carbon::now()]);
			$processed[] = $order->id;
		}

		//--------------------------------------------------------------------------
		// STEP 3: Business office approvers assigned
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process new orders pending approval, fulfillment, collection, complete...');
		}

		$orders = Order::query()
			->whereIn('notice', [
				NoticeStatus::PENDING_APPROVAL,
				NoticeStatus::PENDING_FULFILLMENT,
				NoticeStatus::PENDING_COLLECTION,
				NoticeStatus::COMPLETE
			])
			->whereNotIn('id', $processed)
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			$approvers = array();
			$denied = false;
			foreach ($order->accounts as $account)
			{
				if ($account->approveruserid
				 && !in_array($account->approveruserid, $approvers)
				 && $account->notice == NoticeStatus::ACCOUNT_ASSIGNED)
				{
					array_push($approvers, $account->approveruserid);
				}

				if ($account->notice == NoticeStatus::ACCOUNT_DENIED)
				{
					$denied = true;
				}
			}

			$emailed = array();

			// Send email to each subscriber
			$approvers = array_unique($approvers);

			foreach ($approvers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
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
					$this->comment("  Emailed pending payment approval order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			// Send denied notice if needed
			if ($denied)
			{
				$subscribers = $admins;
				$subscribers[] = $order->userid;
				$subscribers[] = $order->submitteruserid;
				$subscribers = array_unique($subscribers);

				foreach ($subscribers as $subscriber)
				{
					$user = User::find($subscriber);

					if (!$user || !$user->id || $user->trashed())
					{
						continue;
					}

					// Prepare and send actual email
					$message = new PaymentDenied($order, $user);

					if ($this->output->isDebug())
					{
						echo $message->render();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->comment("  Emailed payment denied for order #{$order->id} to {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					if (!$user->email)
					{
						if ($debug || $this->output->isVerbose())
						{
							$this->error("  Email address not found for user {$user->name}.");
						}
						continue;
					}

					Mail::to($user->email)->send($message);
				}
			}

			if ($debug)
			{
				continue;
			}

			// Reset states on accounts
			foreach ($order->accounts as $account)
			{
				$account->update(['notice' => NoticeStatus::NO_NOTICE]);
			}

			// Change states
			if ($order->notice == NoticeStatus::PENDING_APPROVAL)
			{
				$order->update(['notice' => NoticeStatus::PENDING_FULFILLMENT, 'datetimenotified' => Carbon::now()]);
			}
		}

		//--------------------------------------------------------------------------
		// STEP 4: Payment approved, pending fulfillment
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process payment approved, pending fulfillment...');
		}

		$orders = Order::query()
			->whereIn('notice', [NoticeStatus::PENDING_FULFILLMENT])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(NoticeStatus::class . '::' . strtoupper($order->status)) < NoticeStatus::PENDING_FULFILLMENT)
			{
				if ($debug)
				{
					$this->line('  Skipping order #' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			$subscribers = $admins;
			$subscribers[] = $order->userid;
			$subscribers[] = $order->submitteruserid;
			$subscribers = array_unique($subscribers);

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new PaymentApproved($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed pending fulfillment order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			$ticket = false;

			// Do we need to generate ticket?
			foreach ($order->items as $item)
			{
				$product = $item->product;

				if ($product->ticket && (!count($item->sequence()) || $item->isOriginal()))
				{
					$ticket = true;
				}
			}

			if ($ticket)
			{
				$user = new User;
				$user->email = config('mail.from.address');
				$user->name = config('mail.from.name');

				// Prepare and send actual email
				$message = new Ticket($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("  Emailed order #{$order->id} to {$user->email}.");
				}

				if (!$debug)
				{
					Mail::to($user->email)->send($message);
				}
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => NoticeStatus::PENDING_COLLECTION, 'datetimenotified' => Carbon::now()]);
		}

		//--------------------------------------------------------------------------
		// STEP 5: Order fulfilled, pending collection
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process order fulfilled, pending collection...');
		}

		$orders = Order::query()
			->whereIn('notice', [NoticeStatus::PENDING_COLLECTION])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(NoticeStatus::class . '::' . strtoupper($order->status)) < NoticeStatus::PENDING_COLLECTION)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('  Skipping order #' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			$subscribers = $admins;
			$subscribers[] = $order->userid;
			$subscribers[] = $order->submitteruserid;
			$subscribers = array_unique($subscribers);

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new Fulfilled($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed pending collection order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => NoticeStatus::COMPLETE, 'datetimenotified' => Carbon::now()]);

			// Trigger order Fulfilled event
			//
			// Theoretically, this might be backwards. This event
			// should probably be what triggers the email.
			event(new OrderFulfilled($order));
		}

		//--------------------------------------------------------------------------
		// STEP 6: Order collected and complete
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process order collected and complete...');
		}

		$orders = Order::query()
			->whereIn('notice', [NoticeStatus::COMPLETE])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(NoticeStatus::class . '::' . strtoupper($order->status)) < NoticeStatus::COMPLETE)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('  Skipping order #' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			// Send email to each subscriber
			foreach ($admins as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new Complete($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed completed order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => NoticeStatus::NO_NOTICE, 'datetimenotified' => Carbon::now()]);
		}

		//--------------------------------------------------------------------------
		// STEP CANCELED: Order canceled
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process canceled orders...');
		}

		$orders = Order::query()
			->onlyTrashed()
			//->where('notice', '>', 0)
			->whereIn('notice', [NoticeStatus::CANCELED_NOTICE])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(NoticeStatus::class . '::' . strtoupper($order->status)) > NoticeStatus::CANCELED_NOTICE)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('  Skipping order #' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			$subscribers = $admins;
			$subscribers[] = $order->userid;
			$subscribers[] = $order->submitteruserid;
			$subscribers = array_unique($subscribers);

			// Send email to each subscriber
			foreach ($subscribers as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->trashed())
				{
					continue;
				}

				// Prepare and send actual email
				$message = new Canceled($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->comment("  Emailed canceled order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				if (!$user->email)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error("  Email address not found for user {$user->name}.");
					}
					continue;
				}

				Mail::to($user->email)->send($message);
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => NoticeStatus::NO_NOTICE, 'datetimenotified' => Carbon::now()]);
		}
	}
}
