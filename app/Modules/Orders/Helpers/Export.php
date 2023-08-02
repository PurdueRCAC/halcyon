<?php

namespace App\Modules\Orders\Helpers;

use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Modules\Orders\Models\Order;

/**
 * Helper for exporting records to a file
 */
class Export
{
	/**
	 * Get the formatted number.
	 *
	 * @param   array<int,Order> $rows
	 * @param   null|string $export accounts|items
	 * @return  StreamedResponse
	 */
	public static function toCsv($rows, $export = null)
	{
		$data = array();
		$data[] = array(
			//trans('orders::orders.type'),
			trans('orders::orders.id'),
			trans('orders::orders.created'),
			trans('orders::orders.status'),
			trans('orders::orders.submitter'),
			trans('orders::orders.user'),
			trans('orders::orders.group'),
			trans('orders::orders.department'),
			trans('orders::orders.quantity'),
			trans('orders::orders.price'),
			trans('orders::orders.total'),
			'purchaseio',
			'purchasewbse',
			'paymentdocid',
			trans('orders::orders.product'),
			trans('orders::orders.notes'),
		);

		$orders = array();
		foreach ($rows as $row)
		{
			if (in_array($row->id, $orders))
			{
				continue;
			}

			$orders[] = $row->id;

			$submitter = '';
			$user = '';
			$group = '';
			$department = '';

			if ($row->groupid)
			{
				$group = $row->group ? $row->group->name : '';
				if ($row->group)
				{
					$first = $row->group->departmentList()->first();
					if ($first)
					{
						$department = $first->name;
					}
				}
			}

			if ($row->userid)
			{
				$user = $row->user ? $row->user->name : '';
			}

			if ($row->submitteruserid)
			{
				$submitter = $row->submitter ? $row->submitter->name : '';
			}

			//unset($row->state);

			$products = '';
			if ($export != 'items')
			{
				$products = array();
				foreach ($row->items as $item)
				{
					$products[] = $item->product ? $item->product->name : 'product #' . $item->orderproductid;
				}
				$products = implode(', ', $products);
			}

			if ($export != 'accounts')
			{
				$data[] = array(
					//'order',
					$row->id,
					$row->datetimecreated->format('Y-m-d'),
					trans('orders::orders.' . $row->status),
					$submitter,
					$user,
					$group,
					$department,
					'',
					'',
					$row->formatNumber($row->ordertotal),
					'',
					'',
					'',
					$products,
					$row->usernotes
				);
			}

			if ($export == 'items')
			{
				foreach ($row->items()->get() as $item)
				{
					$data[] = array(
						//'item',
						$item->orderid,
						$item->datetimecreated->format('Y-m-d'),
						$item->isFulfilled() ? 'fullfilled' : 'pending',
						$submitter,
						$user,
						$group,
						$department,
						$item->quantity,
						$row->formatNumber($item->origunitprice),
						$row->formatNumber($item->price),
						'',
						'',
						'',
						$item->product ? $item->product->name : $item->orderproductid,
						$row->usernotes
					);
				}
			}

			if ($export == 'accounts')
			{
				foreach ($row->accounts()->get() as $account)
				{
					$data[] = array(
						//'account',
						$account->orderid,
						$account->datetimecreated->format('Y-m-d'),
						trans('orders::orders.' . $account->status),
						$submitter,
						$user,
						$group,
						$department,
						'',
						'',
						$row->formatNumber($account->amount),
						($account->purchaseio ? $account->purchaseio : ''),
						($account->purchasewbse ? $account->purchasewbse : ''),
						($account->paymentdocid ? $account->paymentdocid : ''),
						$products,
						$row->usernotes
					);
				}
			}
		}

		$filename = 'orders_data.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0',
			'Last-Modified' => gmdate('D, d M Y H:i:s') . ' GMT'
		);

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			foreach ($data as $datum)
			{
				fputcsv($file, $datum);
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);
	}
}
