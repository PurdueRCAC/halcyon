<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Users\Models\User;

/**
 * Department approver
 *
 * @property int    $id
 * @property int    $userid
 * @property int    $departmentid
 *
 * @property string $api
 */
class Approver extends Model
{
	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'orderapprovers';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Department
	 *
	 * @return  BelongsTo
	 */
	public function department(): BelongsTo
	{
		return $this->belongsTo(Department::class, 'departmentid');
	}

	/**
	 * User
	 *
	 * @return  BelongsTo
	 */
	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'userid');
	}
}
