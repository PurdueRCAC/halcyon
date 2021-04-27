<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Term field control type
 */
class TermFieldControlType extends Model
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
	protected $primaryKey = 'entitiy_id';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'taxonomy_term__field_control_type';
}
