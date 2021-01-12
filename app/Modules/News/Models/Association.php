<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Models\User;

/**
 * News model mapping to associations
 */
class Association extends Model
{
	/**
	 * The table to which the class pertains
	 * 
	 * @var  string
	 **/
	protected $table = 'newsassociations';

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
	 * @var  array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'newsid'  => 'required|integer',
		'associd' => 'required|integer',
		'assoctype' => 'required|string|max:255',
	);

	/**
	 * Defines a relationship to news article
	 *
	 * @return  object
	 */
	public function article()
	{
		return $this->belongsTo(Article::class, 'newsid');
	}

	/**
	 * Get the associated object
	 *
	 * @return  object
	 */
	public function getAssociatedAttribute()
	{
		$item = null;
		if ($this->assoctype == 'user')
		{
			$item = User::find($this->associd);
		}
		return $item;
	}
}
