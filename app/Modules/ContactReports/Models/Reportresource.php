<?php

namespace App\Modules\ContactReports\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * ContactReports model mapping to resources
 */
class Reportresource extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'contactreportresources';

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
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'contactreportid' => 'positive|nonzero',
		'resourceid' => 'positive|nonzero'
	);

	/**
	 * Defines a relationship to news article
	 *
	 * @return  object
	 */
	public function report()
	{
		return $this->belongsTo(Report::class, 'contactreportid');
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
