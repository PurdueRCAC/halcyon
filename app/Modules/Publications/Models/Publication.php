<?php

namespace App\Modules\Publications\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use App\Modules\History\Traits\Historable;
use App\Modules\Publications\Events\PublicationCreated;
use App\Modules\Publications\Events\PublicationUpdated;
use App\Modules\Publications\Events\PublicationDeleted;
use App\Modules\Publications\Helpers\Formatter;
use App\Modules\Tags\Traits\Taggable;
use Carbon\Carbon;

/**
 * Model for publication
 *
 * @property int    $id
 * @property string $title
 * @property int    $type_id
 * @property string $author
 * @property string $editor
 * @property string $url
 * @property string $series
 * @property string $booktitle
 * @property string $edition
 * @property string $chapter
 * @property string $issuetitle
 * @property string $journal
 * @property string $issue
 * @property string $volume
 * @property string $number
 * @property string $pages
 * @property string $publisher
 * @property string $address
 * @property string $institution
 * @property string $organization
 * @property string $school
 * @property string $crossref
 * @property string $isbn
 * @property string $doi
 * @property string $note
 * @property int    $state
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property string $filename
 *
 * @property string $api
 */
class Publication extends Model
{
	use Historable, SoftDeletes, Taggable;

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
		'created' => PublicationCreated::class,
		'updated' => PublicationUpdated::class,
		'deleted' => PublicationDeleted::class,
	];

	/**
	 * Tag domain
	 *
	 * @var string
	 */
	protected static $entityNamespace = 'publications';

	/**
	 * Get a list of associated users
	 *
	 * @return  HasMany
	 */
	/*public function users(): HasMany
	{
		return $this->hasMany(Map::class, 'publication_id');
	}*/

	/**
	 * Get a list of menu items
	 *
	 * @return  BelongsTo
	 */
	public function type(): BelongsTo
	{
		return $this->belongsTo(Type::class, 'type_id');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  bool  False if error, True on success
	 */
	public function delete(): bool
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
	public function toString(): string
	{
		return strip_tags($this->toHtml());
	}

	/**
	 * Format a publication as HTML
	 * 
	 * @return string
	 */
	public function toHtml(): string
	{
		return Formatter::format($this);
	}

	/**
	 * Is the record published
	 * 
	 * @return bool
	 */
	public function isPublished(): bool
	{
		return ($this->state == 1);
	}

	/**
	 * Is the record unpublished
	 * 
	 * @return bool
	 */
	public function isUnpublished(): bool
	{
		return !$this->isPublished();
	}

	/**
	 * Get authors as an array
	 * 
	 * @return array<int,array<string,string>>
	 */
	public function getAuthorListAttribute(): array
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
	public function path($full = true): string
	{
		return storage_path('app/public/publications/' . $this->id . ($full ? '/' . $this->filename : ''));
	}

	/**
	 * Does this have an attachment
	 *
	 * @return bool
	 */
	public function hasAttachment(): bool
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
	public function sanitize($name): string
	{
		if (!preg_match('/^[\x20-\x7e]*$/', $name))
		{
			$name = \Illuminate\Support\Str::ascii($name);
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

	/**
	 * Query scope with search
	 *
	 * @param   Builder  $query
	 * @param   string|int   $search
	 * @return  Builder
	 */
	public function scopeWhereSearch(Builder $query, $search): Builder
	{
		if (is_numeric($search))
		{
			$query->where('id', '=', $search);
		}
		else
		{
			$filters['search'] = strtolower((string)$search);

			$query->where(function ($where) use ($search)
			{
				$where->where('author', 'like', '%' . $search . '%')
					->orWhere('title', 'like', '%' . $search . '%');
			});
		}

		return $query;
	}

	/**
	 * Query scope with state
	 *
	 * @param   Builder  $query
	 * @param   string   $state
	 * @return  Builder
	 */
	public function scopeWhereState(Builder $query, $state): Builder
	{
		switch ($state)
		{
			case 'unpublished':
				$query->where('state', '=', 0);
			break;

			case 'trashed':
				$query->onlyTrashed();
			break;

			case 'published':
			default:
				$query->where('state', '=', 1);
		}

		return $query;
	}

	/**
	 * Query scope with year
	 *
	 * @param   Builder  $query
	 * @param   string   $year
	 * @return  Builder
	 */
	public function scopeWhereYear(Builder $query, $year): Builder
	{
		$query->where('published_at', '>', $year . '-01-01 00:00:00')
				->where('published_at', '<', Carbon::parse($year . '-01-01 00:00:00')->modify('+1 year')->format('Y') . '-01-01 00:00:00');

		return $query;
	}
}
