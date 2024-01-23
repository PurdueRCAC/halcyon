<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use App\Modules\Groups\Models\Group;
use App\Modules\Users\Models\User;
use App\Modules\Orders\Events\OrderCreated;
use App\Modules\Orders\Events\OrderDeleted;
use App\Modules\Orders\Helpers\Currency;
use Carbon\Carbon;

/**
 * Model for an order
 *
 * @property int    $id
 * @property int    $userid
 * @property int    $submitteruserid
 * @property int    $groupid
 * @property Carbon|string|null $datetimecreated
 * @property Carbon|string|null $datetimeremoved
 * @property Carbon|string|null $datetimenotified
 * @property string $usernotes
 * @property string $staffnotes
 * @property int    $notice
 *
 * @property float $ordertotal
 */
class Order extends Model
{
	use SoftDeletes, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string|null
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var string|null
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var string|null
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'orders';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved'
	];

	/**
	 * The event map for the model.
	 *
	 * @var  array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => OrderCreated::class,
		'deleted' => OrderDeleted::class,
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'datetimenotified' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'datetimecreated';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * Defines a relationship to updates
	 *
	 * @return  HasMany
	 */
	public function items(): HasMany
	{
		return $this->hasMany(Item::class, 'orderid')->where('quantity', '>', 0);
	}

	/**
	 * Defines a relationship to accounts
	 *
	 * @return  HasMany
	 */
	public function accounts(): HasMany
	{
		return $this->hasMany(Account::class, 'orderid');
	}

	/**
	 * Defines a relationship to group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Defines a relationship to user
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Defines a relationship to submitter
	 *
	 * @return  BelongsTo
	 */
	public function submitter(): BelongsTo
	{
		return $this->belongsTo(User::class, 'submitteruserid');
	}

	/**
	 * Set user notes
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setUsernotesAttribute($value): void
	{
		$this->attributes['usernotes'] = strip_tags($value);
	}

	/**
	 * Set staff notes
	 *
	 * @param   string  $value
	 * @return  void
	 */
	public function setStaffnotesAttribute($value): void
	{
		$this->attributes['staffnotes'] = strip_tags($value);
	}

	/**
	 * Get order total
	 *
	 * @return  int
	 */
	public function getTotalAttribute(): int
	{
		$ordertotal = 0;

		foreach ($this->items()->get() as $item)
		{
			$ordertotal += $item->price;
		}

		//return number_format($ordertotal / 100, 2);
		return $ordertotal;
	}

	/**
	 * Format a number into currency
	 *
	 * @param   mixed  $val
	 * @return  string
	 */
	public function formatNumber($val): string
	{
		return Currency::formatNumber($val);
	}

	/**
	 * Is the order canceled?
	 *
	 * @return  bool
	 */
	public function isCanceled(): bool
	{
		return $this->trashed();
	}

	/**
	 * Get order status
	 *
	 * @return  string
	 */
	public function getStatusAttribute(): string
	{
		if ($state = $this->getAttribute('state'))
		{
			switch ($state)
			{
				case 1:
					return 'pending_fulfillment';

				case 2:
					return 'pending_boassignment';

				case 3:
					return 'pending_payment';

				case 4:
					return 'pending_approval';

				case 5:
					return 'pending_collection';

				case 6:
					return 'complete';

				case 7:
					return 'canceled';
			}
		}

		if ($this->trashed())
		{
			$status = 'canceled';
		}
		else
		{
			$its = $this->items;

			$items = count($its);
			$itemsfulfilled = 0;
			$ordertotal = 0;

			foreach ($its as $item)
			{
				if ($item->isFulfilled())
				{
					$itemsfulfilled++;
				}

				$ordertotal += $item->price;
			}

			$acc = $this->accounts;

			$accounts = 0;
			$accountspaid     = 0;
			$accountsapproved = 0;
			$accountsassigned = 0;
			$accountsdenied   = 0;
			$amountassigned   = 0;

			if ($acc)
			{
				$accounts = count($acc);

				foreach ($acc as $account)
				{
					if ($account->approveruserid)
					{
						$accountsassigned++;
					}

					if ($account->isApproved())
					{
						$accountsapproved++;
					}

					if ($account->isPaid())
					{
						$accountspaid++;
					}

					if ($account->isDenied())
					{
						$accountsdenied++;
					}

					$amountassigned += $account->amount;
				}
			}

			if (($accounts == 0 && $ordertotal > 0)
			 || $amountassigned < $ordertotal
			 || ($accountsdenied > 0 && ($accountsdenied + $accountsapproved) == $accounts))
			{
				$status = 'pending_payment';
			}
			elseif ($accountsassigned < $accounts)
			{
				$status = 'pending_boassignment';
			}
			elseif ($accountsapproved < $accounts)
			{
				$status = 'pending_approval';
			}
			elseif ($accountsapproved == $accounts && $itemsfulfilled < $items)
			{
				$status = 'pending_fulfillment';
			}
			elseif ($itemsfulfilled == $items && $accountspaid < $accounts)
			{
				$status = 'pending_collection';
			}
			else
			{
				$status = 'complete';
			}
		}

		return $status;
	}

	/**
	 * Is the order in an active state?
	 *
	 * @return  bool
	 */
	public function isActive(): bool
	{
		return ($this->status != 'canceled' && $this->status != 'complete');
	}

	/**
	 * Format total
	 *
	 * @return  string
	 */
	public function getFormattedTotalAttribute(): string
	{
		return $this->formatNumber($this->total);
	}

	/**
	 * Generate basic stats for a given number of days
	 *
	 * @param   string  $start
	 * @param   string  $stop
	 * @param   int     $recurring
	 * @return  array
	 */
	public static function stats($start, $stop, $recurring = -1): array
	{
		$p = (new Product)->getTable();
		$i = (new Item)->getTable();
		$o = (new self)->getTable();

		$start = Carbon::parse($start);
		$stop  = Carbon::parse($stop);
		$timeframe = round(($stop->timestamp - $start->timestamp) / (60 * 60 * 24));

		$now = Carbon::now();
		$placed = array();
		for ($d = $timeframe; $d >= 0; $d--)
		{
			$yesterday = Carbon::now()->modify('- ' . $d . ' days');
			$tomorrow  = Carbon::now()->modify(($d ? '- ' . ($d - 1) : '+ 1') . ' days');

			$query = self::query();

			if ($recurring >= 0)
			{
				$query->join($i, $i . '.orderid', $o . '.id')
					->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0)
					//->whereNull($i . '.datetimeremoved')
					->select(DB::raw('DISTINCT(' . $o . '.id)'));
			}

			$placed[$yesterday->format('Y-m-d')] = $query
				->where($o . '.datetimecreated', '>', $yesterday->format('Y-m-d') . ' 00:00:00')
				->where($o . '.datetimecreated', '<', $tomorrow->format('Y-m-d') . ' 00:00:00')
				->count();
		}

		//$yesterday = Carbon::now()->modify('- ' . $timeframe . ' days');
		//$tomorrow  = Carbon::now()->modify('+ 1 days');

		$prevyesterday = Carbon::parse($start->format('Y-m-d'))->modify('- ' . $timeframe . ' days');

		// Total
		$query = self::query();
		if ($recurring >= 0)
		{
			$query->join($i, $i . '.orderid', $o . '.id')
				->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0)
				->select($o . '.*')
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.submitteruserid')
				->groupBy($o . '.groupid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes')
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice');
		}
		$past = $query
			->withTrashed()
			->where($o . '.datetimecreated', '>', $start->format('Y-m-d') . ' 00:00:00')
			->where($o . '.datetimecreated', '<', $stop->format('Y-m-d') . ' 00:00:00')
			->get();

		$total = count($past);

		// Total: previous
		$query = self::query();
		if ($recurring >= 0)
		{
			$query->join($i, $i . '.orderid', $o . '.id')
				->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0)
				->select($o . '.*')
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.submitteruserid')
				->groupBy($o . '.groupid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes')
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice');
		}
		$past_prev = $query
			->withTrashed()
			->where($o . '.datetimecreated', '>', $prevyesterday->format('Y-m-d') . ' 00:00:00')
			->where($o . '.datetimecreated', '<', $start->format('Y-m-d') . ' 00:00:00')
			->get();

		$total_prev = count($past_prev);

		// Canceled
		$query = self::query();
		if ($recurring >= 0)
		{
			$query->join($i, $i . '.orderid', $o . '.id')
				->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0)
				//->whereNotNull($i . '.datetimeremoved')
				->select($o . '.*')
				->groupBy($o . '.id')
				->groupBy($o . '.userid')
				->groupBy($o . '.submitteruserid')
				->groupBy($o . '.groupid')
				->groupBy($o . '.datetimecreated')
				->groupBy($o . '.datetimeremoved')
				->groupBy($o . '.usernotes')
				->groupBy($o . '.staffnotes')
				->groupBy($o . '.notice');
		}
		$canc = $query
			->onlyTrashed()
			->where($o . '.datetimecreated', '>', $start->format('Y-m-d') . ' 00:00:00')
			->where($o . '.datetimecreated', '<', $stop->format('Y-m-d') . ' 00:00:00')
			->get();
		$canceled = count($canc);
		$uncharged = 0;
		foreach ($canc as $c)
		{
			$uncharged += $c->total;
		}

		// Canceled: previous
		$query = self::query();
		if ($recurring >= 0)
		{
			$query->join($i, $i . '.orderid', $o . '.id')
				->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0)
				//->whereNotNull($i . '.datetimeremoved')
				->select(DB::raw('DISTINCT(' . $o . '.id)'));
		}
		$canceled_prev = $query
			->onlyTrashed()
			->where($o . '.datetimecreated', '>', $prevyesterday->format('Y-m-d') . ' 00:00:00')
			->where($o . '.datetimecreated', '<', $start->format('Y-m-d') . ' 00:00:00')
			->count();

		$fulfilled = 0;
		$fulfilled_prev = 0;

		$step = array(
			'payment' => array(
				'value' => 0,
				'total' => 0
			),
			'approval' => array(
				'value' => 0,
				'total' => 0
			),
			'fulfilled' => array(
				'value' => 0,
				'total' => 0
			),
			'completed' => array(
				'value' => 0,
				'total' => 0
			),
		);

		$sold = 0;
		$collected = 0;
		foreach ($past as $order)
		{
			$accounts = $order->accounts()
				->orderBy('datetimecreated', 'asc')
				->get();

			$lastapproved = $order->datetimecreated->timestamp;
			if (count($accounts))
			{
				//continue;
			//}

				$step['payment']['value'] += $accounts->first()->datetimecreated->timestamp - $order->datetimecreated->timestamp;
				$step['payment']['total']++;

				
				foreach ($accounts as $account)
				{
					if (!$account->isApproved())
					{
						continue;
					}

					$step['approval']['value'] += $account->datetimeapproved->timestamp - $account->datetimecreated->timestamp;
					$step['approval']['total']++;
					$lastapproved = $account->datetimeapproved->timestamp > $lastapproved ? $account->datetimeapproved->timestamp : $lastapproved;
				}
			}

			$items = $order->items()->orderBy('datetimecreated', 'asc')->get();

			if (!count($items))
			{
				continue;
			}

			foreach ($items as $item)
			{
				if ($item->isFulfilled())
				{
					$fulfilled++;

					$step['fulfilled']['value'] += $item->datetimefulfilled->timestamp - $lastapproved;
					$step['fulfilled']['total']++;

					$step['completed']['value'] += $item->datetimefulfilled->timestamp - $order->datetimecreated->timestamp;
					$step['completed']['total']++;

					$collected += $item->price;
				}
			}

			$sold += $order->total;
		}

		$sold_prev = 0;
		foreach ($past_prev as $order)
		{
			foreach ($order->items as $item)
			{
				if ($item->isFulfilled())
				{
					$fulfilled_prev++;
				}
			}

			$sold_prev += $order->total;
		}

		$avg = $step['payment']['value'] ? $step['payment']['value'] / $step['payment']['total'] : 0;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['payment']['average'] = $avg;

		$avg = $step['approval']['value'] ? $step['approval']['value'] / $step['approval']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['approval']['average'] = $avg;

		$avg = $step['fulfilled']['value'] ? $step['fulfilled']['value'] / $step['fulfilled']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['fulfilled']['average'] = $avg;

		$avg = $step['completed']['value'] ? $step['completed']['value'] / $step['completed']['total'] : null;
		if ($avg)
		{
			$avg = self::toHumanReadable($avg);
		}
		$step['completed']['average'] = $avg;

		// Top products
		$query = Product::query()
			->select($p . '.name', DB::raw('COUNT(*) AS total'))
			->join($i, $i . '.orderproductid', $p . '.id')
			->join($o, $o . '.id', $i . '.orderid')
			->whereNull($p . '.datetimeremoved')
			->whereNull($i . '.datetimeremoved')
			->whereNull($o . '.datetimeremoved')
			->where($i . '.datetimecreated', '>=', $start->toDateTimeString())
			->groupBy($i . '.orderproductid')
			->groupBy($p . '.name')
			->orderBy('total', 'desc')
			->limit(5);
		if ($recurring >= 0)
		{
			$query->where($i . '.origorderitemid', ($recurring ? '>' : '='), 0);
		}
		$products = $query
			->get();
		$topprods = array();
		foreach ($products as $prod)
		{
			$topprods[$prod->name] = $prod->total;
		}

		$stats = array(
			'timeframe'      => $timeframe,
			'submitted'      => $total,
			'submitted_prev' => $total_prev,
			'sold'           => Currency::formatNumber($sold),
			'sold_prev'      => Currency::formatNumber($sold_prev),
			'canceled'       => $canceled,
			'canceled_prev'  => $canceled_prev,
			'uncharged'      => Currency::formatNumber($uncharged),
			'fulfilled'      => $fulfilled,
			'fulfilled_prev' => $fulfilled_prev,
			'collected'      => Currency::formatNumber($collected),
			'steps'          => $step,
			'daily'          => $placed,
			'products'       => $topprods,
		);

		return $stats;
	}

	/**
	 * Generate human readable time
	 *
	 * @param   int  $avg
	 * @return  string
	 */
	public static function toHumanReadable($avg): string
	{
		$unit = '';

		if ($avg < 60)
		{
			$unit = 'sec';
		}
		else if ($avg < 3600)
		{
			$avg /= 60;
			$unit = 'min';
		}
		else if ($avg < 86400)
		{
			$avg /= 3600;
			$unit = 'hrs';
		}
		else
		{
			$avg /= 86400;
			$unit = 'days';
		}

		return round($avg, 2) . ' ' . $unit;
	}
}
