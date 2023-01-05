<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Resources\Models\Asset;

/**
 * News model mapping to resources
 */
class Newsresource extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'newsresources';

	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var  bool
	 */
	public $timestamps = false;

	/**
	 * Default order by for model
	 *
	 * @var  string
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
	 * @var  array<int,string>
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
		'newsid'     => 'required|integer',
		'resourceid' => 'required|integer'
	);

	/**
	 * Defines a relationship to news article
	 *
	 * @return  object
	 */
	public function news()
	{
		return $this->belongsTo(Article::class, 'newsid');
	}

	/**
	 * Defines a relationship to a resource
	 *
	 * @return  object
	 */
	public function resource()
	{
		return $this->belongsTo(Asset::class, 'resourceid')->withTrashed();
	}
}
