<?php
namespace App\Modules\Groups\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Group field of science association
 *
 * @property int    $id
 * @property int    $groupid
 * @property int    $fieldofscienceid
 * @property int    $percentage
 */
class GroupFieldOfScience extends Model
{
	use Historable;

	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'groupfieldofscience';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'percentage';

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
	 * Field of science
	 *
	 * @return  BelongsTo
	 */
	public function field(): BelongsTo
	{
		return $this->belongsTo(FieldOfScience::class, 'fieldofscienceid');
	}

	/**
	 * Group
	 *
	 * @return  BelongsTo
	 */
	public function group(): BelongsTo
	{
		return $this->belongsTo(Group::class, 'groupid');
	}
}
