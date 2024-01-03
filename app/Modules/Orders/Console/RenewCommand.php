<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Account;
use App\Modules\Orders\Models\NoticeStatus;
use Carbon\Carbon;

class RenewCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'orders:renew {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Process auto-renewable orders';

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
			$this->info('Looking up orders to be renewed...');
		}

		$query = Item::query();

		$i = (new Item)->getTable();
		$o = (new Order)->getTable();

		$sequences = $query
			->select(DB::raw('DISTINCT(' . $i . '.origorderitemid)'))
			->join($o, $o . '.id', '=', $i . '.orderid')
			->whereNull($i . '.datetimeremoved')
			->whereNull($o . '.datetimeremoved')
			->where($i . '.origorderitemid', '>', 0)
			//->where($i . '.recurringtimeperiodid', '>', 0)
			->groupBy($i . '.origorderitemid')
			->orderBy($i . '.origorderitemid', 'asc')
			->get();

		$renews = array();
		$now = date("U");

		foreach ($sequences as $seq)
		{
			$sequence = Item::find($seq->origorderitemid);

			if (!$sequence || !$sequence->id)
			{
				continue;
			}

			$until = $sequence->until();

			// Skip orders that haven't even been paid for once
			if (!$until['paid'])
			{
				continue;
			}

			// calculate time to renewal
			$tor = strtotime($until['paid']) - $now;

			$product = $sequence->product;

			// when do we renew?
			$producttime = $product->timeperiod;
			$warningtime = $producttime->warningtimeperiod;

			if ($warningtime)
			{
				$cutoff = Carbon::now()
					->addMonths($warningtime->months)
					->addSeconds($warningtime->unixtime)
					->timestamp;

				$renewthreshold = $cutoff - $now;

				// eligible for renew?
				$renew = false;

				// is this within the renewal window (or overdue) but has been paid at least once
				if ($tor <= $renewthreshold && $tor != -$now)
				{
					$renew = true;
				}
			}

			// don't renew again if we already billed
			if ($renew && $until['paid'] != $until['billed'])
			{
				$renew = false;
			}

			$orderitem = Item::query()
				->where('origorderitemid', '=', $sequence->origorderitemid)
				->orderBy('datetimecreated', 'desc')
				->limit(1)
				->first();

			// don't renew again if the last order was canceled
			if ($renew && (!$orderitem->order || $orderitem->order->isCanceled()))
			{
				$renew = false;
			}

			// don't renew again if the last order had this item removed (quantity = 0)
			if ($renew && $orderitem->quantity == 0)
			{
				$renew = false;
			}

			// don't renew if product is removed
			if ($renew && (!$orderitem->product || $orderitem->product->trashed()))
			{
				$renew = false;
			}

			if ($renew)
			{
				// Group by original order id
				if (!isset($renews[$sequence->orderid]))
				{
					$renews[$sequence->orderid] = array();
				}

				array_push($renews[$sequence->orderid], $sequence->id);
			}
		}

		if (!count($renews))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No renewals found.');
			}
			return;
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Starting renewals of ' . count($renews) . ' orders...');
		}

		foreach ($renews as $orderid => $sequences)
		{
			$items = array();
			$accounts = array();
			$recentaccounts = $orderid;

			// If we sent an itemsequence we are copying another order. GO and fetch all this
			$items = array();
			foreach ($sequences as $sequence)
			{
				// Fetch order information
				// We go newest to oldest so we can fetch the most recent order ID
				// and, thus, get the most recent account info, which is likely to
				// change over time
				$item = Item::query()
					->where('origorderitemid', $sequence)
					->orderBy('datetimecreated', 'desc')
					->limit(1)
					->first();

				if (!$item)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->error('  Failed to find order information for orderitemid #' . $sequence);
					}
					continue;
				}

				$items[] = $item->toArray();
				$recentaccounts = $item->orderid;
			}

			// Fetch accounts information
			$accs = Account::query()
				->where('orderid', '=', $recentaccounts)
				->get();

			foreach ($accs as $account)
			{
				$accounts[] = $account->toArray();
			}

			// Create record
			$order = Order::find($recentaccounts); //$orderid);
			if (!$order)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('  Could not find order #' . $orderid);
				}
				continue;
			}

			$row = new Order;
			$row->userid = $order->userid;
			if ($userid = config('module.orders.user_id'))
			{
				$row->submitteruserid = $userid;
			}
			else
			{
				$row->submitteruserid = $order->submitteruserid;
			}
			$row->groupid = $order->groupid;
			$row->usernotes = '';//$order->usernotes ? 'Order #' . $order->id . " (" . $order->datetimecreated->format('Y-m-d') . "):\n\n" . $order->usernotes : '';
			$row->staffnotes = ''; //$order->staffnotes ? 'Order #' . $order->id . " (" . $order->datetimecreated->format('Y-m-d') . "):\n\n" . $order->staffnotes : '';
			$row->notice = NoticeStatus::PENDING_PAYMENT;

			if ($debug)
			{
				$this->comment('  Copying order #' . $orderid);
			}
			else
			{
				$row->save();
			}

			// Create each item in order
			foreach ($items as $i)
			{
				$item = new Item;
				$item->orderid = $row->id;
				$item->orderproductid = $i['orderproductid'];
				if (isset($i['product']))
				{
					$item->orderproductid = $i['product'];
				}
				$item->quantity = $i['quantity'];
				if (isset($i['origorderitemid']))
				{
					$item->origorderitemid = $i['origorderitemid'];
				}
				if (isset($i['recurringtimeperiodid']))
				{
					$item->recurringtimeperiodid = $i['recurringtimeperiodid'];
				}
				if (isset($i['timeperiodcount']))
				{
					$item->timeperiodcount = $i['timeperiodcount'];
				}

				$total = $item->product->unitprice * $item->quantity;

				$item->price = $total;
				if (isset($i['price']))
				{
					$item->price = $i['price'];
				}
				$item->origunitprice = $item->product->unitprice;

				if ($debug)
				{
					if ($debug || $this->output->isVerbose())
					{
						$this->line('  |_ Copying item #' . $i['id']);
					}
					continue;
				}

				$item->save();
			}

			if (!empty($accounts))
			{
				foreach ($accounts as $a)
				{
					$account = new Account;
					$account->amount              = $a['amount'];
					$account->purchasefund        = $a['purchasefund'];
					$account->purchasecostcenter  = $a['purchasecostcenter'];
					$account->purchaseorder       = $a['purchaseorder'];
					$account->purchaseio          = $a['purchaseio'];
					$account->purchasewbse        = $a['purchasewbse'];
					$account->budgetjustification = $a['budgetjustification'];
					//$account->approveruserid      = $a['approveruserid'];
					$account->orderid = $row->id;

					if ($debug || $this->output->isVerbose())
					{
						$this->line('  |_ Copying account #' . $a['id']);
						continue;
					}

					$account->saveQuietly();
				}
			}
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Finished.');
		}
	}
}
