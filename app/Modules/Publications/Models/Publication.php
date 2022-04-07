<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use App\Modules\Publications\Events\PublicationCreated;
use App\Modules\Publications\Events\PublicationUpdated;
use App\Modules\Publications\Events\PublicationDeleted;
use App\Modules\Publications\Helpers\Formatter;

/**
 * Model for publication
 */
class Publication extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'publications';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'published_at';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var  array
	 */
	protected $dates = [
		'published_at',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => PublicationCreated::class,
		'updated'  => PublicationUpdated::class,
		'deleted'  => PublicationDeleted::class,
	];

	/**
	 * Get a list of menu items
	 *
	 * @return  object
	 */
	public function authors()
	{
		return $this->hasMany(Author::class, 'publication_id');
	}

	/**
	 * Get a list of menu items
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'type_id');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @param   array    $options
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		// Delete the module items
		foreach ($this->authors as $row)
		{
			$row->delete();
		}

		// Attempt to delete the record
		return parent::delete($options);
	}

	/**
	 * Format a publication
	 * 
	 * @return string
	 */
	public function toString()
	{
		return strip_tags($this->toHtml());
	}

	/**
	 * Format a publication as HTML
	 * 
	 * @return string
	 */
	public function toHtml()
	{
		return Formatter::format($this);
	}

	/**
	 * Is the record published
	 * 
	 * @return string
	 */
	public function isPublished()
	{
		return ($this->state == 1);
	}

	/**
	 * Is the record unpublished
	 * 
	 * @return string
	 */
	public function isUnpublished()
	{
		return !$this->isPublished();
	}
}
