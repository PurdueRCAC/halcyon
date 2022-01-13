<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Users\Models\User;
use App\Modules\News\Events\AssociationCreated;
use App\Modules\News\Events\AssociationDeleted;

/**
 * News model mapping to associations
 */
class Association extends Model
{
	use SoftDeletes;

	/**
	 * The table to which the class pertains
	 * 
	 * @var  string
	 **/
	protected $table = 'newsassociations';

	/**
	 * The name of the "created at" column.
	 *
	 * @var  string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

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
	 * The event map for the model.
	 *
	 * @var  array
	 */
	protected $dispatchesEvents = [
		'created'  => AssociationCreated::class,
		'deleted'  => AssociationDeleted::class,
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
