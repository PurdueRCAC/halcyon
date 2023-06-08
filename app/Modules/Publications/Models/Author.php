<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Publications\Events\AuthorCreated;
use App\Modules\Publications\Events\AuthorUpdated;
use App\Modules\Publications\Events\AuthorDeleted;

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
}
