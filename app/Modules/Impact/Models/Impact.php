<?php

namespace App\Modules\Impact\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\History\Traits\Historable;

/**
 * Model for an impact entry
 *
 * @property int    $id
 * @property string $name
 * @property string $value
 * @property int    $impacttableid
 * @property int    $sequence
 * @property Carbon|null $updateddatetime
 */
class Impact extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'impacts';

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
	public static $orderBy = 'sequence';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<sint,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'name' => 'required|string|max:255',
		'value' => 'required|string|max:255',
		'impacttableid' => 'required|integer',
	);

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var  array<string,string>
	 */
	protected $casts = [
		'updateddatetime' => 'datetime:Y-m-d H:i:s',
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
