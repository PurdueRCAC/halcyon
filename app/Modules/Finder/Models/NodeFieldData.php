<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Node field data
 */
class NodeFieldData extends Model
{
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
	protected $table = 'node_field_data';
}
