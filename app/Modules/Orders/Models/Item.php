<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use App\Modules\Orders\Events\ItemUpdated;
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
		'datetimecreated',
		'datetimeremoved',
		'datetimefulfilled'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var  array
	 */
	protected $dispatchesEvents = [
		'updated'  => ItemUpdated::class,
	];

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
		return $this->belongsTo(Order::class, 'orderid')->withTrashed();
	}

	/**
	 * Defines a relationship to product
	 *
	 * @return  object
	 */
	public function product()
	{
		return $this->hasOne(Product::class, 'id', 'orderproductid')->withTrashed();
	}

	/**
	 * If account is fulfilled
	 *
	 * @return  bool
	 **/
	public function isFulfilled()
	{
		return (!is_null($this->datetimefulfilled));
	}

	/**
	 * If item is recurring
	 *
	 * @return  bool
	 **/
	public function isRecurring()
	{
		return ($this->origorderitemid > 0);
	}

	/**
	 * If item is recurring
	 *
	 * @return  bool
	 **/
	public function isOriginal()
	{
		return ($this->origorderitemid == $this->id);
	}

	/**
	 * Calculate billing and paid until...
	 *
	 * @return  array
	 **/
	public function until()
	{
		$datebilleduntil = null;
		$datepaiduntil   = null;
		$paidperiods   = 0;
		$billedperiods = 0;

		$datestart = $this->datetimefulfilled;

		$data = self::query()
			->where('origorderitemid', '=', $this->origorderitemid)
			->orderBy('datetimecreated', 'asc')
			->get();

		foreach ($data as $row)
		{
			if ($row->isFulfilled())
			{
				$paidperiods += $row->timeperiodcount;
			}

			if (!$row->trashed() && ($row->order && !$row->order->trashed()))
			{
				$billedperiods += $row->timeperiodcount;
			}
		}

		if ($datestart)
		{
			// Get the timeperiod
			$timeperiod = $this->product->timeperiod;

			if ($timeperiod)
			{
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
		}

		return array(
			'billed' => $datebilleduntil,
			'paid'   => $datepaiduntil
		);
	}

	/**
	 * If item is trashed
	 *
	 * @return  mixed  Carbon|null
	 **/
	public function getPaiduntilAttribute()
	{
		$until = $this->until();
		return $until['paid'] ? Carbon::parse($until['paid']) : null;
	}

	/**
	 * If item is trashed
	 *
	 * @return  mixed  Carbon|null
	 **/
	public function getBilleduntilAttribute()
	{
		$until = $this->until();
		return $until['billed'] ? Carbon::parse($until['billed']) : null;
	}

	/**
	 * Recurrence range
	 *
	 * @return  array
	 **/
	public function recurrenceRange()
	{
		$recur_months  = $this->product->timeperiod ? $this->product->timeperiod->months : 0;
		$recur_seconds = $this->product->timeperiod ? $this->product->timeperiod->unixtime : 0;

		$datestart = null;

		$data = self::query()
			->where('origorderitemid', '=', $this->origorderitemid)
			->orderBy('datetimecreated', 'asc')
			->get();

		$items = array();
		$users = array();
		$groups = array();

		foreach ($data as $row)
		{
			if ($row->id == $this->origorderitemid)
			{
				$datestart = $row->datetimefulfilled ? $row->datetimefulfilled : $row->datetimecreated;
			}

			if (!$row->order)
			{
				continue;
			}

			$users[] = $row->order->userid;
			$users[] = $row->order->submitteruserid;
			$groups[] = $row->order->groupid;

			/*if (!$row->trashed())
			{
				//$item['start'] = $datestart;
				$item->start = $datestart;

				//$start = Carbon::parse($item['start']);
				$start = Carbon::parse($item->start);

				if ($recur_months || $recur_seconds)
				{
					$start->modify('+' . ($recur_months * $item['timeperiodcount']) . ' months')
						->modify('+' . ($recur_seconds * $item['timeperiodcount']) . ' seconds');
				}

				//$item['end'] = $start->toDateTimeString();
				$item->end = $start;

				$datestart = $item['end'];
			}
			else
			{
				$item['start'] = null;
				$item['end']   = null;
			}
			
			$items[] = $item;*/
			if (!$row->trashed())
			{
				$row->start = $datestart;

				$start = Carbon::parse($row->start);

				if ($recur_months || $recur_seconds)
				{
					$start->modify('+' . ($recur_months * $row->timeperiodcount) . ' months')
						->modify('+' . ($recur_seconds * $row->timeperiodcount) . ' seconds');
				}

				$row->end = $start;

				$datestart = $row->end;
			}
			else
			{
				$row->start = null;
				$row->end = null;
			}

			$items[] = $row;
		}

		$this->orderusers = array_values(array_unique($users));
		$this->ordergroups = array_values(array_unique($groups));

		return collect($items);
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
				/*if ($item['id'] == $this->id)
				{
					if ($item['start'])
					{
						$this->setAttribute('start_at', Carbon::parse($item['start']));
						$this->setAttribute('end_at', Carbon::parse($item['end']));
					}
				}*/
				if ($item->id == $this->id)
				{
					if ($item->start)
					{
						$this->setAttribute('start_at', $item->start);
						$this->setAttribute('end_at', $item->end);
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
				/*if ($item['id'] == $this->id)
				{
					if ($item['end'])
					{
						$this->setAttribute('start_at', Carbon::parse($item['start']));
						$this->setAttribute('end_at', Carbon::parse($item['end']));
					}
				}*/
				if ($item->id == $this->id)
				{
					if ($item->end)
					{
						$this->setAttribute('start_at', $item->start);
						$this->setAttribute('end_at', $item->end);
					}
				}
			}
		}

		return $this->end_at;
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getFormattedPriceAttribute()
	{
		return $this->formatCurrency($this->origunitprice);
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function getFormattedTotalAttribute()
	{
		return $this->formatCurrency($this->price);
	}

	/**
	 * Format unit price
	 *
	 * @return  string
	 */
	public function formatCurrency($val)
	{
		$number = preg_replace('/[^0-9\-]/', '', $val);

		$neg = '';
		if ($number < 0)
		{
			$neg = '-';
			$number = -$number;
		}

		if ($number > 99)
		{
			$dollars = substr($number, 0, strlen($number) - 2);
			$cents   = substr($number, strlen($number) - 2, 2);
			$dollars = number_format($dollars);

			$number = $dollars . '.' . $cents;
		}
		elseif ($number > 9 && $number < 100)
		{
			$number = '0.' . $number;
		}
		else
		{
			$number = '0.0' . $number;
		}

		return $neg . $number;
	}

	/**
	 * Get sequence
	 *
	 * @return  object
	 */
	public function sequence()
	{
		if (!$this->origorderitemid)
		{
			return collect([]);
		}

		$i = (new self)->getTable();
		$o = (new Order)->getTable();

		$sequences = self::query()
			//->withTrashed()
			->select($i . '.*')//DB::raw('DISTINCT(' . $i . '.origorderitemid)'))
			->join($o, $o . '.id', '=', $i . '.orderid')
			//->whereNull($i . '.datetimeremoved')
			->whereNull($o . '.datetimeremoved')
			->where($i . '.origorderitemid', '=', $this->origorderitemid)
			//->where($i . '.recurringtimeperiodid', '>', 0)
			//->groupBy($i . '.origorderitemid')
			->orderBy($i . '.id', 'desc')
			->get();

		return $sequences;
	}
}
