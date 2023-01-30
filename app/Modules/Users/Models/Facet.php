<?php
namespace App\Modules\Users\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Users\Events\FacetCreated;
use App\Modules\Users\Events\FacetDeleted;

/**
 * User facet model
 *
 * This should probably be called `Attribute` but
 * the name causes conflicts with Laravel
 */
class Facet extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'user_facets';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public static $orderBy = 'key';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The event map for the model.
	 *
	 * @var array<string, string>
	 */
	protected $dispatchesEvents = [
		'created' => FacetCreated::class,
		'deleted' => FacetDeleted::class,
	];

	/**
	 * Get parent user
	 *
	 * @return  object
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	/**
	 * Find record by user ID and key
	 *
	 * @param   int  $user_id
	 * @param   string   $key
	 * @return  Facet|null
	 */
	public static function findByUserAndKey($user_id, $key)
	{
		return self::query()
			->where('user_id', '=', (int)$user_id)
			->where('key', '=', (string)$key)
			->first();
	}
}
