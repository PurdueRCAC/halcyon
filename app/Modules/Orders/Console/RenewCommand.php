<?php
/**
 * @package    framework
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

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
			if ($until['paid'] == '0000-00-00 00:00:00')
			{
				continue;
			}

			// calculate time to renewal
			$tor = strtotime($until['paid']) - $now;

			$product = $sequence->product;

			// when do we renew?
			$producttime = $product->timeperiod;
			$warningtime = $producttime->warningtime;

			$cutoff = Carbon::now()->addMonths($warningtime->months)->addSeconds($warningtime->unixtime)->timestamp;
			//$cutoff = strtotime($db->getInterval(date("Y-m-d H:i:s", $now), $warningtime->months, $warningtime->unixtime));
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
			if ($renew && $product->isDeleted())
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

		foreach ($renews as $renew)
		{
			//$neworder = new stdClass();
			//$neworder->orderitemsequence = $renew;

			//$neworder = Order::blank();
			//$ws->post(ROOT_URI . "order", $neworder);
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
			 ->addOverview('Email Updates')
			 ->addTasks($this)
			 ->render();
	}
}
