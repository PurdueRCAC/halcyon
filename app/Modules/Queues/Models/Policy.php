<?php
namespace App\Modules\Queues\Models;

use Illuminate\Database\Eloquent\Model;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;

/**
 * Model for a scheduler policy
 */
class Policy extends Model
{
	use ErrorBag, Validatable, Historable;

	/**
	 * The name of the "created at" column.
	 *
	 * @var string
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'schedulers';

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'hostname' => 'notempty'
	);

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Defines a relationship to schedulers
	 *
	 * @return  object
	 */
	public function schedulers()
	{
		return $this->hasMany(Queue::class, 'schedulerpolicyid');
	}
}
