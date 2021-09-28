<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Mail\PendingPayment;
use App\Modules\Orders\Mail\PendingAssignment;
use App\Modules\Orders\Mail\PendingApproval;
use App\Modules\Orders\Mail\PaymentDenied;
use App\Modules\Orders\Mail\PaymentApproved;
use App\Modules\Orders\Mail\Ticket;
use App\Modules\Orders\Mail\Fulfilled;
use App\Modules\Orders\Mail\Complete;
use App\Modules\Orders\Mail\Canceled;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use App\Halcyon\Access\Map;

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
	 * Order notice states
	 *
	 * @var integer
	 */
	const PENDING_PAYMENT = 1;
	const PENDING_BOASSIGNMENT = 2;
	const PENDING_APPROVAL = 3;
	const PENDING_FULFILLMENT = 4;
	const PENDING_COLLECTION = 6;
	const COMPLETE = 7;
	const CANCELED = -1;
	const NO_NOTICE = 0;
	const CANCELED_NOTICE = 8;
	const ACCOUNT_ASSIGNED = 3;
	const ACCOUNT_APPROVED = 4;
	const ACCOUNT_DENIED = 5;

	/**
	 * Execute the console command.
	 */
	public function handle()
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
			->where('notice', '=', self::PENDING_PAYMENT)
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

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed new order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed new order #{$order->id}.");
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->offsetUnset('type');
			$order->update(['notice' => self::PENDING_BOASSIGNMENT]);
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
			->where('notice', '=', self::PENDING_BOASSIGNMENT)
			->whereNotIn('id', $processed)
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(self::class . '::' . strtoupper($order->status)) < self::PENDING_BOASSIGNMENT)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('skipping ' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			// Send email to each subscriber
			foreach ($admins as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed pending payment info order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed pending payment info order #{$order->id}.");
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => self::PENDING_APPROVAL]);
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
			->whereIn('notice', [self::PENDING_APPROVAL, self::PENDING_FULFILLMENT, self::PENDING_COLLECTION, self::COMPLETE])
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
				 && $account->notice == self::ACCOUNT_ASSIGNED)
				{
					array_push($approvers, $account->approveruserid);
				}

				if ($account->notice == self::ACCOUNT_DENIED)
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

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed pending payment approval order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed pending payment approval order #{$order->id}.");
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

					if (!$user || !$user->id || $user->isTrashed())
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
						$this->info("Emailed payment denied for order #{$order->id} to {$user->email}.");

						if ($debug)
						{
							continue;
						}
					}

					Mail::to($user->email)->send($message);

					$this->log($user->id, $order->id, $user->email, "Emailed payment denied for order #{$order->id}.");
				}
			}

			if ($debug)
			{
				continue;
			}

			// Reset states on accounts
			foreach ($order->accounts as $account)
			{
				$account->update(['notice' => self::NO_NOTICE]);
			}

			// Change states
			if ($order->notice == self::PENDING_APPROVAL)
			{
				$order->update(['notice' => self::PENDING_FULFILLMENT]);
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
			->whereIn('notice', [self::PENDING_FULFILLMENT])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(self::class . '::' . strtoupper($order->status)) < self::PENDING_FULFILLMENT)
			{
				if ($debug)
				{
					$this->line('skipping ' . $order->id . ' - ' . $order->status);
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

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed pending fulfillment order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed pending fulfillment order #{$order->id}.");
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
				$user->name = config('app.name');

				// Prepare and send actual email
				$message = new Ticket($order, $user);

				if ($this->output->isDebug())
				{
					echo $message->render();
				}

				if ($debug || $this->output->isVerbose())
				{
					$this->info("Emailed order #{$order->id} to {$user->email}.");
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
			$order->update(['notice' => self::PENDING_COLLECTION]);
		}

		//--------------------------------------------------------------------------
		// STEP 5: Order fulfilled, pending collection
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process order fulfilled, pending collection...');
		}

		$orders = Order::query()
			->whereIn('notice', [self::PENDING_COLLECTION])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(self::class . '::' . strtoupper($order->status)) < self::PENDING_COLLECTION)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('skipping ' . $order->id . ' - ' . $order->status);
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

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed pending collection order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed pending collection order #{$order->id}.");
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => self::COMPLETE]);
		}

		//--------------------------------------------------------------------------
		// STEP 6: Order collected and complete
		//--------------------------------------------------------------------------
		if ($debug || $this->output->isVerbose())
		{
			$this->info('Process order collected and complete...');
		}

		$orders = Order::query()
			->whereIn('notice', [self::COMPLETE])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(self::class . '::' . strtoupper($order->status)) < self::COMPLETE)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('skipping ' . $order->id . ' - ' . $order->status);
				}
				continue;
			}

			// Send email to each subscriber
			foreach ($admins as $subscriber)
			{
				$user = User::find($subscriber);

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed completed order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed completed order #{$order->id}.");
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => self::NO_NOTICE]);
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
			->whereIn('notice', [self::CANCELED_NOTICE])
			->orderBy('id', 'asc')
			->get();

		foreach ($orders as $order)
		{
			if (constant(self::class . '::' . strtoupper($order->status)) > self::CANCELED_NOTICE)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('skipping ' . $order->id . ' - ' . $order->status);
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

				if (!$user || !$user->id || $user->isTrashed())
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
					$this->info("Emailed canceled order #{$order->id} to {$user->email}.");

					if ($debug)
					{
						continue;
					}
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $order->id, $user->email, "Emailed canceled order #{$order->id}.");
			}

			if ($debug)
			{
				continue;
			}

			// Change states
			$order->update(['notice' => self::NO_NOTICE]);
		}
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  null
	 */
	protected function log($targetuserid, $targetobjectid = 0, $uri = '', $payload = '')
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => Str::limit($uri, 128, ''),
			'app'             => Str::limit('email', 20, ''),
			'payload'         => Str::limit($payload, 2000, ''),
			'classname'       => Str::limit('orders:emailstatus', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
