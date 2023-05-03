<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;
use App\Modules\Resources\Models\Asset;

/**
 * Group node model
 *
 * @property int    $id
 * @property string $year
 * @property int    $resourceid
 * @property int    $groupid
 * @property int    $maxnodes
 * @property int    $proratednodes
 */
class Node extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupnodes';

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
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Get parent group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Get associated resource
	 *
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo(Asset::class, 'resourceid');
	}
}
