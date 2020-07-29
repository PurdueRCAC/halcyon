<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;
use Carbon\Carbon;

/**
 * NEws model mapping to resources
 */
class Item extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'orderitems';

	/**
	 * Automatic fields to populate every time a row is created
	 *
	 * @var  array
	 */
	protected $dates = array(
		'datetimecreated'
	);

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Defines a relationship to order
	 *
	 * @return  object
	 */
	public function order()
	{
		return $this->hasOne(Order::class, 'orderid');
	}

	/**
	 * Defines a relationship to product
	 *
	 * @return  object
	 */
	public function product()
	{
		return $this->hasOne(Product::class, 'id', 'orderproductid');
	}

	/**
	 * If account is fulfilled
	 *
	 * @return  bool
	 **/
	public function isFulfilled()
	{
		return ($this->datetimefulfilled && $this->datetimefulfilled != '0000-00-00 00:00:00');
	}

	/**
	 * Calculate billing and paid until...
	 *
	 * @return  array
	 **/
	public function until()
	{
		$datebilleduntil = '0000-00-00 00:00:00';
		$datepaiduntil   = '0000-00-00 00:00:00';

		$datestart = $this->datetimefulfilled;

		if ($datestart != '0000-00-00 00:00:00')
		{
			// Get the timeperiod
			$timeperiod = $this->product->timeperiod;

			$recur_months   = $timeperiod->months;
			$recur_seconds  = $timeperiod->unixtime;

			// Calculate billed time
			$months_billed  = $billedperiods * $recur_months;
			$seconds_billed = $billedperiods * $recur_seconds;

			$datebilleduntil = with(Carbon::parse($datestart))
				->modify('+ ' . $months_billed . ' month')
				->modify('+ ' . $seconds_billed . ' second')
				->format('Y-m-d H:i:s');

			// Calculate paid time
			$months_paid    = $paidperiods * $recur_months;
			$seconds_paid   = $paidperiods * $recur_seconds;

			$datepaiduntil = (Carbon::parse($datestart))
				->modify('+ ' . $months_paid . ' month')
				->modify('+ ' . $seconds_paid . ' second')
				->format('Y-m-d H:i:s');
		}

		return array(
			'billed' => $datebilleduntil,
			'paid'   => $datepaiduntil
		);
	}

	/**
	 * If account is fulfilled
	 *
	 * @return  bool
	 **/
	public function recurrenceRange()
	{
		$recur_months  = $this->product->timeperiod->months;
		$recur_seconds = $this->product->timeperiod->unixtime;

		$datestart = '0000-00-00 00:00:00';

		$data = self::query()
			->where('datetimeremoved', '=', '0000-00-00 00:00:00')
			->where('origorderitemid', '=', $this->origorderitemid)
			->orderBy('datetimecreated', 'asc')
			->get();

		$items = array();

		foreach ($data as $row)
		{
			if ($row->id == $this->origorderitemid)
			{
				$datestart = $row->datetimefulfilled;
			}

			$item = $row->toArray();

			if ($row->datetimeremoved->toDateTimeString() == '-0001-11-30 00:00:00')// == '0000-00-00 00:00:00')
			{
				$item['start'] = $datestart;

				$start = Carbon::parse($item['start']);
				$start->modify('+' . ($recur_months * $item['timeperiodcount']) . ' months')
					->modify('+' . ($recur_seconds * $item['timeperiodcount']) . ' seconds');

				$item['end'] = $start->toDateTimeString();

				$datestart = $item['end'];
			}
			else
			{
				$item['start'] = '0000-00-00 00:00:00';
				$item['end']   = '0000-00-00 00:00:00';
			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * If account is fulfilled
	 *
	 * @return  bool
	 **/
	public function start()
	{
		if ($this->start_at === null)
		{
			$this->setAttribute('start_at', 0);

			foreach ($this->recurrenceRange() as $item)
			{
				if ($item['id'] == $this->id)
				{
					if ($item['start'] != '0000-00-00 00:00:00')
					{
						$this->setAttribute('start_at', Carbon::parse($item['start']));
						$this->setAttribute('end_at', Carbon::parse($item['end']));
					}
				}
			}
		}

		return $this->start_at;
	}

	/**
	 * If account is fulfilled
	 *
	 * @return  bool
	 **/
	public function end()
	{
		if ($this->end_at === null)
		{
			$this->setAttribute('end_at', 0);

			foreach ($this->recurrenceRange() as $item)
			{
				if ($item['id'] == $this->id)
				{
					if ($item['end'] != '0000-00-00 00:00:00')
					{
						$this->setAttribute('start_at', Carbon::parse($item['start']));
						$this->setAttribute('end_at', Carbon::parse($item['end']));
					}
				}
			}
		}

		return $this->end_at;
	}
}
