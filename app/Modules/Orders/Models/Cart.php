<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for order cart
 */
class Cart extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'ordercarts';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'identifier',
		'instance'
	];
}
