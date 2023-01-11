<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use App\Modules\History\Traits\Historable;
use App\Modules\Publications\Events\PublicationCreated;
use App\Modules\Publications\Events\PublicationUpdated;
use App\Modules\Publications\Events\PublicationDeleted;
use App\Modules\Publications\Helpers\Formatter;

/**
 * Model for publication
 */
class Publication extends Model
{
	use Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'publications';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'published_at';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'desc';

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
	 * @var  array<string,string>
	 */
	protected $casts = [
		'published_at' => 'datetime:Y-m-d H:i:s',
	];

	/**
	 * The event map for the model.
	 *
	 * @var array<string,string>
	 */
	protected $dispatchesEvents = [
		'created'  => PublicationCreated::class,
		'updated'  => PublicationUpdated::class,
		'deleted'  => PublicationDeleted::class,
	];

	/**
	 * Get a list of associated users
	 *
	 * @return  object
	 */
	/*public function users()
	{
		return $this->hasMany(Map::class, 'publication_id');
	}*/

	/**
	 * Get a list of menu items
	 *
	 * @return  object
	 */
	public function type()
	{
		return $this->belongsTo(Type::class, 'type_id');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete()
	{
		$this->deleteAttachment();

		// Attempt to delete the record
		return parent::delete();
	}

	/**
	 * Format a publication
	 * 
	 * @return string
	 */
	public function toString()
	{
		return strip_tags($this->toHtml());
	}

	/**
	 * Format a publication as HTML
	 * 
	 * @return string
	 */
	public function toHtml()
	{
		return Formatter::format($this);
	}

	/**
	 * Is the record published
	 * 
	 * @return bool
	 */
	public function isPublished()
	{
		return ($this->state == 1);
	}

	/**
	 * Is the record unpublished
	 * 
	 * @return bool
	 */
	public function isUnpublished()
	{
		return !$this->isPublished();
	}

	/**
	 * Get authors as an array
	 * 
	 * @return array<int,array>
	 */
	public function getAuthorListAttribute()
	{
		$authors = $this->author;
		$items = array();

		if (strstr($authors, ';'))
		{
			$auths = explode(';', $authors);
		}
		else
		{
			$authors = str_replace(' and ', ',', $authors);
			$auths = explode('.,', $authors);
		}

		foreach ($auths as $i => $auth)
		{
			$author = trim($auth) . '.';
			$author = str_replace('..', '.', $author);
			$item = array();

			$author_arr = explode(',', $author);
			$author_arr = array_map('trim', $author_arr);
			if (count($author_arr) < 2)
			{
				$author_arr = explode(' ', $author);
				$first = array_shift($author_arr);
				$last = array_pop($author_arr);
				foreach ($author_arr as $leftover)
				{
					$first .= ' ' . $leftover;
				}

				$item['first'] = (!empty($first)) ? trim($first) : '';
				$item['last']  = (!empty($last)) ? trim($last) : '';
			}
			else
			{
				$item['first'] = (isset($author_arr[1])) ? $author_arr[1] : '';
				$item['last']  = (isset($author_arr[0])) ? $author_arr[0] : '';
			}

			$items[] = $item;
		}

		return $items;
	}

	/**
	 * Does this have an attachment
	 *
	 * @param bool $full
	 * @return string
	 */
	public function path($full = true)
	{
		return storage_path('app/public/publications/' . $this->id . ($full ? '/' . $this->filename : ''));
	}

	/**
	 * Does this have an attachment
	 *
	 * @return bool
	 */
	public function hasAttachment()
	{
		if (!$this->id)
		{
			return false;
		}
		return file_exists($this->path());
	}

	/**
	 * Does this have an attachment
	 *
	 * @return Attachment|false
	 */
	public function getAttachmentAttribute()
	{
		return $this->hasAttachment() ? new Attachment($this->path()) : false;
	}

	/**
	 * Does this have an attachment
	 *
	 * @return mixed
	 */
	public function deleteAttachment()
	{
		if ($this->hasAttachment())
		{
			return unlink($this->path());
		}

		return true;
	}

	/**
	 * Sanitize file names
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function sanitize($name)
	{
		if (!preg_match('/^[\x20-\x7e]*$/', $name))
		{
			$name = \Illuminate\Support\Facades\Str::ascii($name);
		}
		$name = preg_replace(
			'~
			[<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
			[\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
			[\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
			[#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
			[{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
			~x',
			'-', $name
		);
		// avoids ".", ".." or ".hiddenFiles"
		$name = ltrim($name, '.-');

		// reduce consecutive characters
		$name = preg_replace(array(
			// "file   name.zip" becomes "file-name.zip"
			'/ +/',
			// "file---name.zip" becomes "file-name.zip"
			'/-+/'
		), '-', $name);
		$name = preg_replace(
			// "file___name.zip" becomes "file_name.zip"
			'/_+/', '_', $name);
		$name = preg_replace(array(
			// "file--.--.-.--name.zip" becomes "file.name.zip"
			'/-*\.-*/',
			// "file__.__._.__name.zip" becomes "file.name.zip"
			'/_*\._*/',
			// "file...name..zip" becomes "file.name.zip"
			'/\.{2,}/'
		), '.', $name);
		// lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
		//$name = mb_strtolower($name, mb_detect_encoding($name));
		// ".file-name.-" becomes "file-name"
		$name = trim($name, '.-_');

		return $name;
	}
}
