<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * News model for stemmed text
 *
 * @property int    $id
 * @property string $stemmedtext
 */
class Stemmedtext extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'newsstemmedtext';

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
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'stemmedtext' => 'required|string|max:20000'
	);

	/**
	 * Defines a relationship to an article
	 *
	 * @return  BelongsTo
	 */
	public function article(): BelongsTo
	{
		return $this->belongsTo(Article::class, 'id');
	}
}
