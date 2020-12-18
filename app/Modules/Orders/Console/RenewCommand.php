<?php

namespace App\Modules\Orders\Console;

use Illuminate\Console\Command;
use App\Modules\Orders\Models\Item;
use App\Modules\Orders\Models\Order;
use Carbon\Carbon;

class RenewCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'orders:renew';

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
		$this->info('Renewing orders...');
		return;

		$query = Item::query();

		$i = Item::getTable();
		$o = Order::getTable();

		$query
			->select($i . '.origorderitemid')
			->join($o, $o . '.id', '=', $i . '.orderid')
			->groupBy('origorderitemid')
			->orderBy('origorderitemid', 'asc');

		$sequences = $query->get();
		$renews = array();
		$now = date("U");

		foreach ($sequences as $sequence)
		{
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
			$warningtime = $producttime->warningtime;

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

			// don't renew again if we already billed
			if ($renew && $until['paid'] != $until['billed'])
			{
				$renew = false;
			}

			// don't renew again if the last order was canceled
			if ($renew && $sequence->orderitems[count($sequence->orderitems)-1]['ordercanceled'] != '0000-00-00 00:00:00')
			{
				$renew = false;
			}

			// don't renew again if the last order had this item removed (quantity = 0)
			if ($renew && $sequence->orderitems[count($sequence->orderitems)-1]['quantity'] == '0')
			{
				$renew = false;
			}

			// don't renew if product is removed
			if ($renew && $product->isTrashed())
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

		foreach ($renews as $sequences)
		{
			//$neworder = new Order;
			//$neworder->orderitemsequence = $renew;

			//Order::createFromSequence($renew);

			$items = array();
			$orderid = 0;

			// If we sent an itemsequence we are copying another order. GO and fetch all this
			$items = array();
			foreach ($sequences as $sequence)
			{
				// Fetch order information
				$item = Item::query()
					->where('origorderitemid', $sequence)
					->where(function($where)
					{
						$where->whereNull('datetimeremoved')
							->orWhere('datetimeremoved', '=', '0000-00-00 00:00:00');
					})
					->orderBy('datetimecreated', 'desc')
					->limit(1)
					->first();

				if (!$item)
				{
					$this->warning('Failed to find order information for orderitemid #' . $sequence);
					continue;
				}

				//create a new class.
				$item->id = null;
				$item->datetimecreated = null;

				$items[] = (array)$item;

				$orderid = $item->orderid;

				//$row->userid  = $item->userid;
				//$row->groupid = $item->groupid;
			}

			// Fetch accounts information
			/*$accounts = Account::query()
				->where('orderid', '=', $orderid)
				->get();*/

			// Create record
			$row = Order::find($orderid);
			//$row->userid = $request->input('userid', auth()->user() ? auth()->user()->id : 0);
			//$row->groupid = $request->input('groupid', 0);
			//$row->submitteruserid = $request->input('submitteruserid', $row->userid);
			//$row->usernotes = $request->input('usernotes', '');
			//$row->staffnotes = $request->input('staffnotes', '');
			$row->id = null;
			$row->datetimecreated = null;
			$row->notice = 1; //$request->input('notice', 1);
			$row->save();

			// Create each item in order
			foreach ($items as $i)
			{
				$item = new Item;
				$item->fill($i);
				$item->orderid = $row->id;
				//$item->orderproductid = $i['product'];
				//$item->quantity = $i['quantity'];
				//$item->origorderitemid = $i['origorderitemid'];
				//$item->recurringtimeperiod
				//$item->origunitprice = $i['origunitprice'];

				$total = $item->product->unitprice * $item->quantity;

				$item->price = $total;
				if (isset($i['price']))
				{
					$item->price = $i['price'];
				}
				$item->origunitprice = $item->product->unitprice;

				if ($total != $item->price)
				{
					$this->warning('Total and item price do not match');
					continue;
				}

				$item->save();
			}
		}
	}

	/**
	 * Output help documentation
	 *
	 * @return  void
	 **/
	public function help()
	{
		$this->output
			 ->getHelpOutput()
			 ->addOverview('Process auto-renewable orders')
			 ->addTasks($this)
			 ->render();
	}
}
