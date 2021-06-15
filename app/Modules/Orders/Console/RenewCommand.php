<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\Account;
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
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		if ($debug)
		{
			$this->info('Renewing orders...');
		}

		$query = Item::query();

		$i = (new Item)->getTable();
		$o = (new Order)->getTable();

		$sequences = $query
			->select(DB::raw('DISTINCT(' . $i . '.origorderitemid)'))
			->join($o, $o . '.id', '=', $i . '.orderid')
			->where(function($where) use ($i)
			{
				$where->whereNull($i . '.datetimeremoved')
					->orWhere($i . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($o)
			{
				$where->whereNull($o . '.datetimeremoved')
					->orWhere($o . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
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
			if (!$until['paid'] || $until['paid'] == '0000-00-00 00:00:00')
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

			$orderitems = Item::query()
				->withTrashed()
				->whereIsActive()
				->where('origorderitemid', '=', $sequence->origorderitemid)
				->orderBy('datetimecreated', 'asc')
				->get();

			// don't renew again if the last order was canceled
			if ($renew && $orderitems[count($orderitems)-1]->order->isCanceled())
			{
				$renew = false;
			}

			// don't renew again if the last order had this item removed (quantity = 0)
			if ($renew && $orderitems[count($orderitems)-1]->quantity == 0)
			{
				$renew = false;
			}

			// don't renew if product is removed
			if ($renew && $sequence->product->isTrashed())
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
			if ($debug)
			{
				$this->info('No renewals found.');
			}
			return;
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
					->withTrashed()
					->whereIsActive()
					->where('origorderitemid', $sequence)
					->orderBy('datetimecreated', 'desc')
					->limit(1)
					->first();

				if (!$item)
				{
					$this->error('Failed to find order information for orderitemid #' . $sequence);
					continue;
				}

				$items[] = $item->toArray();
				$recentaccounts = $item->orderid;
			}

			// Fetch accounts information
			$accs = Account::query()
				->withTrashed()
				->whereIsActive()
				->where('orderid', '=', $recentaccounts)
				->get();

			foreach ($accs as $account)
			{
				$accounts[] = $account->toArray();
			}

			// Create record
			$order = Order::find($orderid);
			if (!$order)
			{
				$this->error('Could not find order #' . $orderid);
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
			$row->usernotes = $order->usernotes;
			$row->staffnotes = $order->staffnotes;
			$row->notice = 1;

			if ($debug)
			{
				$this->info('Copying order #' . $orderid);
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
					$this->info('Copying order #' . $orderid . ' item #' . $i['id']);
					continue;
				}

				$item->save();
			}

			if (!empty($accounts))
			{
				foreach ($accounts as $a)
				{
					$account = new Account;
					$account->amount              = 0;
					$account->purchasefund        = $a['purchasefund'];
					$account->purchasecostcenter  = $a['purchasecostcenter'];
					$account->purchaseorder       = $a['purchaseorder'];
					$account->purchaseio          = $a['purchaseio'];
					$account->purchasewbse        = $a['purchasewbse'];
					$account->budgetjustification = $a['budgetjustification'];
					//$account->approveruserid      = $a['approveruserid'];
					$account->orderid = $row->id;

					if ($debug)
					{
						$this->info('Copying order #' . $orderid . ' account #' . $a['id']);
						continue;
					}

					$account->save();
				}
			}
		}
	}
}
