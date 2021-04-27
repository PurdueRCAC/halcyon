<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Term field data
 */
class TermRevisionFieldControlType extends Model
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
	protected $primaryKey = 'tid';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'taxonomy_term_revision__field_control_type';
}
