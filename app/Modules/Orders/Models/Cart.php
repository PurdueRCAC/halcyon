<?php
namespace App\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for order cart
 *
 * @property string $identifier
 * @property string $instance
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Cart extends Model
{
	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'ordercarts';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'identifier',
		'instance'
	];
}
