<?php
/**
 * @package   halcyon-cms
 * @copyright Copyright 2005-2015 Halcyon Foundation, LLC.
 * @license   http://opensource.org/licenses/MIT MIT
 */

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
	 * @return  boolean
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
	 * @return  string
	 */
	public function setMetadescAttribute($metadesc)
	{
		return (is_null($metadesc) ? '' : $metadesc);
	}

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	public function setMetakeyAttribute($metakey)
	{
		return (is_null($metakey) ? '' : $metakey);
	}

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	public function setMetadataAttribute($metadata)
	{
		return (is_null($metadata) ? '' : $metadata);
	}

	/**
	 * Parses content string as directed
	 *
	 * @return  integer
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

	/**
	 * Parses content string as directed
	 *
	 * @return  string
	 */
	/*public function getContentAttribute($content)
	{
		if (!isset($this->contentParsed))
		{
			//event('onPrepareContent', [&$content]);
			//$this->contentParsed = view('pages::basic')->with(compact('content'))->render();
			$this->contentParsed = Blade::compileString($content);
		}

		return $this->contentParsed;
	}*/
}
