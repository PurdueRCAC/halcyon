<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Issues model mapping to resources
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
	 * Fields and their validation criteria
	 *
	 * @var array<string,string>
	 */
	protected $rules = array(
		'issueid' => 'positive|nonzero',
		'resourceid' => 'positive|nonzero'
	);

	/**
	 * Defines a relationship to an issue
	 *
	 * @return  object
	 */
	public function issue()
	{
		return $this->belongsTo(Issue::class, 'issueid');
	}

	/**
	 * Defines a relationship to resources
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo('App\Modules\Resources\Models\Asset', 'resourceid');
	}
}
