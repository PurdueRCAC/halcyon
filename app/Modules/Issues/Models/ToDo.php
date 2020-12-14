<?php

namespace App\Modules\Issues\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Halcyon\Utility\PorterStemmer;
use App\Halcyon\Models\Timeperiod;
use App\Modules\History\Traits\Historable;
use App\Modules\Issues\Events\ReportPrepareContent;
use App\Modules\Users\Models\User;

/**
 * Issue model
 */
class ToDo extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
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
	 * @var string
	 */
	const DELETED_AT = 'datetimeremoved';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'issuetodos';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
		'datetimecreated',
		'datetimeremoved'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'datetimecreated',
		'datetimeremoved'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'userid' => 'required|integer',
		'name' => 'required|string|max:255',
		'description' => 'required|string|max:2000'
	);

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function issues()
	{
		return $this->hasMany(Issue::class, 'issuetodoid');
	}

	/**
	 * Defines a relationship to resources map
	 *
	 * @return  object
	 */
	public function timeperiod()
	{
		return $this->belongsTo(Timeperiod::class, 'recurringtimeperiodid');
	}

	/**
	 * Defines a relationship to creator
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo(User::class, 'userid');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->issues as $issue)
		{
			if (!$issue->delete($options))
			{
				$this->addError($issue->getError());
				return false;
			}
		}

		// Attempt to delete the record
		return parent::delete($options);
	}

		/**
	 * Query scope where record isn't trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsActive($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNull($t . '.datetimeremoved')
					->orWhere($t . '.datetimeremoved', '=', '0000-00-00 00:00:00');
		});
	}

	/**
	 * Query scope where record is trashed
	 *
	 * @param   object  $query
	 * @return  object
	 */
	public function scopeWhereIsTrashed($query)
	{
		$t = $this->getTable();

		return $query->where(function($where) use ($t)
		{
			$where->whereNotNull($t . '.datetimeremoved')
				->where($t . '.datetimeremoved', '!=', '0000-00-00 00:00:00');
		});
	}
}
