<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Publications\Events\AuthorCreated;
use App\Modules\Publications\Events\AuthorUpdated;
use App\Modules\Publications\Events\AuthorDeleted;
use Carbon\Carbon;

/**
 * Model for publication author
 *
 * @property int    $id
 * @property string $name
 * @property int    $publication_id
 * @property int    $user_id
 * @property int    $ordering
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Author extends Model
{
	use SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'publication_authors';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'ordering';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created' => AuthorCreated::class,
		'updated' => AuthorUpdated::class,
		'deleted' => AuthorDeleted::class,
	];

	/**
	 * Get parent publication
	 *
	 * @return  BelongsTo
	 */
	public function publication(): BelongsTo
	{
		return $this->belongsTo(Publication::class, 'publication_id');
	}

	/**
	 * Method to move a row in the ordering sequence of a group of rows defined by an SQL WHERE clause.
	 * Negative numbers move the row up in the sequence and positive numbers move it down.
	 *
	 * @param   int     $delta  The direction and magnitude to move the row in the ordering sequence.
	 * @param   string  $where  WHERE clause to use for limiting the selection of rows to compact the ordering values.
	 * @return  bool    True on success.
	 */
	public function move($delta): bool
	{
		// If the change is none, do nothing.
		if (empty($delta))
		{
			return true;
		}

		// Select the primary key and ordering values from the table.
		$query = self::query()
			->where('publication_id', '=', $this->publication_id);

		// If the movement delta is negative move the row up.
		if ($delta < 0)
		{
			$query->where('ordering', '<', (int) $this->ordering);
			$query->orderBy('ordering', 'desc');
		}
		// If the movement delta is positive move the row down.
		elseif ($delta > 0)
		{
			$query->where('ordering', '>', (int) $this->ordering);
			$query->orderBy('ordering', 'asc');
		}

		// Select the first row with the criteria.
		$row = $query->first();

		// If a row is found, move the item.
		if ($row)
		{
			$prev = $this->ordering;

			// Update the ordering field for this instance to the row's ordering value.
			if (!$this->update(['ordering' => (int) $row->ordering]))
			{
				return false;
			}

			// Update the ordering field for the row to this instance's ordering value.
			if (!$row->update(['ordering' => (int) $prev]))
			{
				return false;
			}
		}

		$all = self::query()
			->where('publication_id', '=', $this->publication_id)
			->orderBy('ordering', 'asc')
			->get();

		foreach ($all as $i => $row)
		{
			if ($row->ordering != ($i + 1))
			{
				$row->update(['ordering' => $i + 1]);
			}
		}

		return true;
	}
}
