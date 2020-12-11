<?php

namespace App\Halcyon\Auth\Link;

use App\Halcyon\Database\Relational;
use Carbon\Carbon;

/**
 * Authentication Link data
 */
class Data extends Relational
{
	/**
	 * The table namespace
	 *
	 * @var  string
	 */
	protected $namespace = 'auth_link';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 */
	protected $table = 'auth_link_data';

	/**
	 * Default order by for model
	 *
	 * @var  string
	 */
	public $orderBy = 'id';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'link_id'    => 'positive|nonzero',
		'domain_key' => 'notempty'
	);

	/**
	 * Automatically fillable fields
	 *
	 * @var  array
	 **/
	public $always = array(
		'modified'
	);

	/**
	 * Generates automatic modified field value
	 *
	 * @param   array   $data  the data being saved
	 * @return  string
	 */
	public function automaticModified($data)
	{
		if (!isset($data['modified']) || !$data['modified'])
		{
			$data['modified'] = Carbon::now()->toDateTimeString();
		}
		return $data['modified'];
	}

	/**
	 * Defines a belongs to one relationship between entry and Link
	 *
	 * @return  object
	 */
	public function link()
	{
		return $this->belongsToOne('App\Halcyon\Auth\Link', 'link_id');
	}

	/**
	 * Get an instance of a record
	 *
	 * @param   integer  $link_id
	 * @param   string   $domain_key
	 * @return  mixed    Object on success, False on failure
	 */
	public static function oneByLinkAndKey($link_id, $domain_key)
	{
		$row = self::all()
			->whereEquals('link_id', $link_id)
			->whereEquals('domain_key', $domain_key)
			->row();

		if (!$row || !$row->get('id'))
		{
			$row = self::blank();
		}

		return $row;
	}
}
