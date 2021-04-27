<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Finder Node access
 */
class NodeAccess extends Model
{
	use Historable;

	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The primary key for the model.
	 *
	 * @var string
	 */
	protected $primaryKey = 'nid';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'node_access';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'nid'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'realm' => 'required|string|max:255',
		'gid' => 'required|integer'
	);
}
