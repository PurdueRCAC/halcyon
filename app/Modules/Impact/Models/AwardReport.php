<?php

namespace App\Modules\Impact\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Impact award reports
 *
 * @property int    $id
 * @property int    $fiscalyear
 * @property int    $awards
 * @property int    $totalawards
 * @property int    $awardeecount
 * @property int    $totalawardeecount
 */
class AwardReport extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'awardreports';

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
	public $orderBy = 'fiscalyear';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'desc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];
}
