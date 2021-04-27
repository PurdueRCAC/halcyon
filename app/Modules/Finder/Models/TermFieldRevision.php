<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Term field revision
 */
class TermFieldRevision extends Model
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
	protected $primaryKey = 'revision_id';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'taxonomy_term_field_revision';
}
