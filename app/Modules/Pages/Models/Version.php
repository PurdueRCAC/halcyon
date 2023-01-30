<?php

namespace App\Modules\Pages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

/**
 * Model class for a page version
 */
class Version extends Model
{
	/**
	 * Indicates if the model should be timestamped.
	 *
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'page_versions';

	/**
	 * Parsed content
	 *
	 * @var  string
	 */
	protected $contentParsed = null;

	/**
	 * The model's default values for attributes.
	 *
	 * @var array<string,int>
	 */
	protected $attributes = [
		'version' => 1,
	];

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array<string,string>
	 */
	protected $casts = [
		'page_id' => 'integer',
		'version' => 'integer',
		'created_by' => 'integer',
		'length' => 'integer'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array<string,string>
	 */
	protected $rules = array(
		'page_id' => 'required|integer|min:1',
		'content' => 'required|string',
	);

	/**
	 * Does the page exist?
	 *
	 * @return  bool
	 */
	public function exists()
	{
		return !!$this->getAttribute('id');
	}

	/**
	 * Defines a belongs to one relationship between task and liaison
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Defines a belongs to one relationship to the parent page
	 *
	 * @return  object
	 */
	public function page()
	{
		return $this->belongsTo(Page::class, 'page_id');
	}

	/**
	 * Parses content string as directed
	 *
	 * @param   string|null  $metadesc
	 * @return  string
	 */
	public function setMetadescAttribute($metadesc)
	{
		return (is_null($metadesc) ? '' : $metadesc);
	}

	/**
	 * Parses content string as directed
	 *
	 * @param   string|null  $metakey
	 * @return  string
	 */
	public function setMetakeyAttribute($metakey)
	{
		return (is_null($metakey) ? '' : $metakey);
	}

	/**
	 * Parses content string as directed
	 *
	 * @param   string|null  $metadata
	 * @return  string
	 */
	public function setMetadataAttribute($metadata)
	{
		return (is_null($metadata) ? '' : $metadata);
	}

	/**
	 * Parses content string as directed
	 *
	 * @param   int  $version
	 * @return  int
	 */
	public function setVersionAttribute($version)
	{
		$version = intval($version);
		return $version++;
	}

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	public function setLengthAttribute($length)
	{
		return strlen($this->getAttribute('content'));
	}
}
