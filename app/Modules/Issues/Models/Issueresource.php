<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Issues model mapping to resources
 *
 * @property int $id
 * @property int $issueid
 * @property int $resourceid
 */
class Issueresource extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'issueresources';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Defines a relationship to an issue
	 *
	 * @return  BelongsTo
	 */
	public function issue(): BelongsTo
	{
		return $this->belongsTo(Issue::class, 'issueid');
	}

	/**
	 * Defines a relationship to resources
	 *
	 * @return  BelongsTo
	 */
	public function resource(): BelongsTo
	{
		return $this->belongsTo('App\Modules\Resources\Models\Asset', 'resourceid');
	}
}
