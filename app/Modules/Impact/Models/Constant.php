<?php

namespace App\Modules\Impact\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Impact contact
 *
 * @property int    $id
 * @property string $name
 * @property string $value
 * @property int    $impacttableid
 * @property int    $sequence
 */
class Constant extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'impactconstants';

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
	public $orderBy = 'sequence';

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
	 * Defines a relationship to an impact table
	 *
	 * @return  BelongsTo
	 */
	public function table(): BelongsTo
	{
		return $this->belongsTo(Table::class, 'impacttableid');
	}
}
