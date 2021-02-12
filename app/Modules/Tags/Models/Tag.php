<?php

namespace App\Modules\Tags\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Halcyon\Traits\ErrorBag;
use App\Halcyon\Traits\Validatable;
use App\Modules\History\Traits\Historable;
use App\Modules\Tags\Events\TagCreated;
use App\Modules\Tags\Events\TagUpdated;
use App\Modules\Tags\Events\TagDeleted;
use Carbon\Carbon;

/**
 * Tag model
 */
class Tag extends Model
{
	use ErrorBag, Validatable, Historable, SoftDeletes;

	/**
	 * The table to which the class pertains
	 *
	 * @var string
	 **/
	protected $table = 'tags';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = array(
		'name' => 'required'
	);

	/**
	 * The event map for the model.
	 *
	 * @var array
	 */
	protected $dispatchesEvents = [
		'created'  => TagCreated::class,
		'updated'  => TagUpdated::class,
		'deleted'  => TagDeleted::class,
	];

	/**
	 * Normalize tag input
	 *
	 * @param   string  $tag
	 * @return  string
	 */
	public function normalize($tag)
	{
		$transliterationTable = array(
			'á' => 'a', 'Á' => 'A', 'à' => 'a', 'À' => 'A', 'ă' => 'a', 'Ă' => 'A', 'â' => 'a', 'Â' => 'A', 'å' => 'a', 'Å' => 'A', 'ã' => 'a', 'Ã' => 'A', 'ą' => 'a', 'Ą' => 'A', 'ā' => 'a', 'Ā' => 'A', 'ä' => 'ae', 'Ä' => 'AE', 'æ' => 'ae', 'Æ' => 'AE',
			'ḃ' => 'b', 'Ḃ' => 'B',
			'ć' => 'c', 'Ć' => 'C', 'ĉ' => 'c', 'Ĉ' => 'C', 'č' => 'c', 'Č' => 'C', 'ċ' => 'c', 'Ċ' => 'C', 'ç' => 'c', 'Ç' => 'C',
			'ď' => 'd', 'Ď' => 'D', 'ḋ' => 'd', 'Ḋ' => 'D', 'đ' => 'd', 'Đ' => 'D', 'ð' => 'dh', 'Ð' => 'Dh',
			'é' => 'e', 'É' => 'E', 'è' => 'e', 'È' => 'E', 'ĕ' => 'e', 'Ĕ' => 'E', 'ê' => 'e', 'Ê' => 'E', 'ě' => 'e', 'Ě' => 'E', 'ë' => 'e', 'Ë' => 'E', 'ė' => 'e', 'Ė' => 'E', 'ę' => 'e', 'Ę' => 'E', 'ē' => 'e', 'Ē' => 'E',
			'ḟ' => 'f', 'Ḟ' => 'F', 'ƒ' => 'f', 'Ƒ' => 'F',
			'ğ' => 'g', 'Ğ' => 'G', 'ĝ' => 'g', 'Ĝ' => 'G', 'ġ' => 'g', 'Ġ' => 'G', 'ģ' => 'g', 'Ģ' => 'G',
			'ĥ' => 'h', 'Ĥ' => 'H', 'ħ' => 'h', 'Ħ' => 'H',
			'í' => 'i', 'Í' => 'I', 'ì' => 'i', 'Ì' => 'I', 'î' => 'i', 'Î' => 'I', 'ï' => 'i', 'Ï' => 'I', 'ĩ' => 'i', 'Ĩ' => 'I', 'į' => 'i', 'Į' => 'I', 'ī' => 'i', 'Ī' => 'I',
			'ĵ' => 'j', 'Ĵ' => 'J',
			'ķ' => 'k', 'Ķ' => 'K',
			'ĺ' => 'l', 'Ĺ' => 'L', 'ľ' => 'l', 'Ľ' => 'L', 'ļ' => 'l', 'Ļ' => 'L', 'ł' => 'l', 'Ł' => 'L',
			'ṁ' => 'm', 'Ṁ' => 'M',
			'ń' => 'n', 'Ń' => 'N', 'ň' => 'n', 'Ň' => 'N', 'ñ' => 'n', 'Ñ' => 'N', 'ņ' => 'n', 'Ņ' => 'N',
			'ó' => 'o', 'Ó' => 'O', 'ò' => 'o', 'Ò' => 'O', 'ô' => 'o', 'Ô' => 'O', 'ő' => 'o', 'Ő' => 'O', 'õ' => 'o', 'Õ' => 'O', 'ø' => 'oe', 'Ø' => 'OE', 'ō' => 'o', 'Ō' => 'O', 'ơ' => 'o', 'Ơ' => 'O', 'ö' => 'oe', 'Ö' => 'OE',
			'ṗ' => 'p', 'Ṗ' => 'P',
			'ŕ' => 'r', 'Ŕ' => 'R', 'ř' => 'r', 'Ř' => 'R', 'ŗ' => 'r', 'Ŗ' => 'R',
			'ś' => 's', 'Ś' => 'S', 'ŝ' => 's', 'Ŝ' => 'S', 'š' => 's', 'Š' => 'S', 'ṡ' => 's', 'Ṡ' => 'S', 'ş' => 's', 'Ş' => 'S', 'ș' => 's', 'Ș' => 'S', 'ß' => 'SS',
			'ť' => 't', 'Ť' => 'T', 'ṫ' => 't', 'Ṫ' => 'T', 'ţ' => 't', 'Ţ' => 'T', 'ț' => 't', 'Ț' => 'T', 'ŧ' => 't', 'Ŧ' => 'T',
			'ú' => 'u', 'Ú' => 'U', 'ù' => 'u', 'Ù' => 'U', 'ŭ' => 'u', 'Ŭ' => 'U', 'û' => 'u', 'Û' => 'U', 'ů' => 'u', 'Ů' => 'U', 'ű' => 'u', 'Ű' => 'U', 'ũ' => 'u', 'Ũ' => 'U', 'ų' => 'u', 'Ų' => 'U', 'ū' => 'u', 'Ū' => 'U', 'ư' => 'u', 'Ư' => 'U', 'ü' => 'ue', 'Ü' => 'UE',
			'ẃ' => 'w', 'Ẃ' => 'W', 'ẁ' => 'w', 'Ẁ' => 'W', 'ŵ' => 'w', 'Ŵ' => 'W', 'ẅ' => 'w', 'Ẅ' => 'W',
			'ý' => 'y', 'Ý' => 'Y', 'ỳ' => 'y', 'Ỳ' => 'Y', 'ŷ' => 'y', 'Ŷ' => 'Y', 'ÿ' => 'y', 'Ÿ' => 'Y',
			'ź' => 'z', 'Ź' => 'Z', 'ž' => 'z', 'Ž' => 'Z', 'ż' => 'z', 'Ż' => 'Z',
			'þ' => 'th', 'Þ' => 'Th', 'µ' => 'u',
			'а' => 'a', 'А' => 'a', 'б' => 'b',
			'Б' => 'b', 'в' => 'v', 'В' => 'v',
			'г' => 'g', 'Г' => 'g', 'д' => 'd',
			'Д' => 'd', 'е' => 'e', 'Е' => 'e',
			'ё' => 'e', 'Ё' => 'e', 'ж' => 'zh',
			'Ж' => 'zh', 'з' => 'z', 'З' => 'z',
			'и' => 'i', 'И' => 'i', 'й' => 'j',
			'Й' => 'j', 'к' => 'k', 'К' => 'k',
			'л' => 'l', 'Л' => 'l', 'м' => 'm',
			'М' => 'm', 'н' => 'n', 'Н' => 'n',
			'о' => 'o', 'О' => 'o', 'п' => 'p',
			'П' => 'p', 'р' => 'r', 'Р' => 'r',
			'с' => 's', 'С' => 's', 'т' => 't',
			'Т' => 't', 'у' => 'u', 'У' => 'u',
			'ф' => 'f', 'Ф' => 'f', 'х' => 'h',
			'Х' => 'h', 'ц' => 'c', 'Ц' => 'c',
			'ч' => 'ch', 'Ч' => 'ch', 'ш' => 'sh',
			'Ш' => 'sh', 'щ' => 'sch', 'Щ' => 'sch',
			'ъ' => '', 'Ъ' => '', 'ы' => 'y',
			'Ы' => 'y', 'ь' => '', 'Ь' => '',
			'э' => 'e', 'Э' => 'e', 'ю' => 'ju',
			'Ю' => 'ju', 'я' => 'ja', 'Я' => 'ja'
		);

		$tag = str_replace(array_keys($transliterationTable), array_values($transliterationTable), $tag);
		return strtolower(preg_replace("/[^a-zA-Z0-9]/", '', $tag));
	}

	/**
	 * Creator profile
	 *
	 * @return  object
	 */
	public function creator()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'created_by');
	}

	/**
	 * Determine if record was modified
	 *
	 * @return  boolean  True if modified, false if not
	 */
	public function isUpdated()
	{
		if ($this->updated_at
		 && $this->updated_at != $this->created_at)
		{
			return true;
		}
		return false;
	}

	/**
	 * Creator profile
	 *
	 * @return  object
	 */
	public function updater()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'updated_by');
	}

	/**
	 * Creator profile
	 *
	 * @return  object
	 */
	public function trasher()
	{
		return $this->belongsTo('App\Modules\Users\Models\User', 'deleted_by');
	}

	/**
	 * Get a list of aliases
	 *
	 * @return  object
	 */
	public function aliases()
	{
		return $this->hasMany(Alias::class, 'tag_id');
	}

	/**
	 * Get a comma-separated list of aliases
	 *
	 * @return  string
	 */
	public function getAliasStringAttribute()
	{
		$subs = array();

		foreach ($this->aliases as $sub)
		{
			$subs[] = $sub->name;
		}

		return implode(', ', $subs);
	}

	/**
	 * Get a list of objects
	 *
	 * @return  object
	 */
	public function tagged()
	{
		return $this->hasMany(Tagged::class, 'tag_id');
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean  False if error, True on success
	 */
	public function delete(array $options = [])
	{
		foreach ($this->aliases as $row)
		{
			$row->delete();
		}

		foreach ($this->tagged as $row)
		{
			$row->delete();
		}

		return parent::delete($options);
	}

	/**
	 * Save entry
	 *
	 * @return  object
	 */
	/*public function save(array $options = array())
	{
		$action = $this->isNew() ? 'tag_created' : 'tag_edited';

		$result = parent::save();

		if ($result)
		{
			$log = Log::blank();
			$log->tag_id = $this->id;
			$log->action = $action;
			$log->comments = $this->toJson();
			$log->save();
		}

		//$this->purgeCache();

		return $result;
	}*/

	/**
	 * Retrieves one row loaded by a tag field
	 *
	 * @param   string  $tag  The tag to load by
	 * @return  mixed
	 **/
	public static function findByTag($tag)
	{
		$instance = self::query();

		$model = $instance->where('slug', '=', $instance->normalize($tag))->limit(1)->get();

		if (!$model->id)
		{
			$sub = Alias::blank()
				->where('slug', '=', $instance->normalize($tag))
				->limit(1)
				->get()
				->first();

			if ($tag_id = $sub->tag_id)
			{
				$model = self::oneOrNew($tag_id);
			}
		}

		return $model;
	}

	/**
	 * Remove this tag from an object
	 *
	 * If $taggerid is provided, it will only remove the tags added to an object by
	 * that specific user
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   integer  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   integer  $tagger    User ID of person to filter tag by
	 * @return  boolean
	 */
	public function removeFrom($scope, $scope_id, $tagger=0)
	{
		// Check if the relationship exists
		$to = Tagged::findByScoped($scope, $scope_id, $this->id, $tagger);

		if (!$to->id)
		{
			return true;
		}

		// Attempt to delete the record
		if (!$to->delete())
		{
			$this->addError($to->getError());
			return false;
		}

		$this->tagged_count = $this->tagged()->count();

		return $this->save();
	}

	/**
	 * Add this tag to an object
	 *
	 * @param   string   $scope     Object type (ex: resource, ticket)
	 * @param   integer  $scope_id  Object ID (e.g., resource ID, ticket ID)
	 * @param   integer  $tagger    User ID of person adding tag
	 * @param   integer  $strength  Tag strength
	 * @return  boolean
	 */
	public function addTo($scope, $scope_id, $tagger = 0, $strength = 1)
	{
		// Check if the relationship already exists
		$to = Tagged::findByScoped($scope, $scope_id, $this->id, $tagger);

		if ($to->id)
		{
			return true;
		}

		// Set some data
		$to->taggable_type = (string) $scope;
		$to->taggable_id = (int) $scope_id;
		$to->tag_id = (int) $this->id;
		$to->strength = (int) $strength;
		$to->created_by = $tagger ? $tagger : auth()->user()->id;

		// Attempt to store the new record
		if (!$to->save())
		{
			$this->addError($to->getError());
			return false;
		}

		$this->tagged_count = $this->tagged()->count();

		return $this->save();
	}

	/**
	 * Move all data from this tag to another, including the tag itself
	 *
	 * @param   integer  $tag_id  ID of tag to merge with
	 * @return  boolean
	 */
	public function mergeWith($tag_id)
	{
		if (!$tag_id)
		{
			$this->addError(trans('Missing tag ID.'));
			return false;
		}

		// Get all the associations to this tag
		// Loop through the associations and link them to a different tag
		if (!Tagged::moveTo($this->id, $tag_id))
		{
			$this->addError(trans('Failed to move objects attached to tag.'));
			return false;
		}

		// Get all the substitutions to this tag
		// Loop through the records and link them to a different tag
		if (!Alias::moveTo($this->id, $tag_id))
		{
			$this->addError(trans('Failed to move aliases attached to tag.'));
			return false;
		}

		// Make the current tag a substitute for the new tag
		$sub = Alias::blank();
		$sub->name   = $this->name;
		$sub->tag_id = $tag_id;
		if (!$sub->save())
		{
			$this->addError($sub->getError());
			return false;
		}

		// Update new tag's counts
		$tag = self::find($tag_id);
		$tag->tagged_count = $tag->tagged()->count();
		$tag->alias_count = $tag->aliases()->count();
		$tag->save();

		// Destroy the old tag
		if (!$this->delete())
		{
			return false;
		}

		return true;
	}

	/**
	 * Copy associations from this tag to another
	 *
	 * @param   integer  $tag_id  ID of tag to copy associations to
	 * @return  boolean
	 */
	public function copyTo($tag_id)
	{
		if (!$tag_id)
		{
			$this->addError(trans('Missing tag ID.'));
			return false;
		}

		// Get all the associations to this tag
		// Loop through the associations and link them to a different tag
		if (!Tagged::copyTo($this->id, $tag_id))
		{
			$this->addError($to->getError());
			return false;
		}

		// Update new tag's counts
		$tag = self::find($tag_id);
		$tag->tagged_count = $tag->tagged()->count();
		$tag->save();

		return true;
	}

	/**
	 * Save tag substitutions
	 *
	 * @param   string   $tag_string
	 * @return  boolean
	 */
	public function saveSubstitutions($tag_string='')
	{
		// Get the old list of substitutions
		$subs = array();
		foreach ($this->aliases as $sub)
		{
			$subs[$sub->slug] = $sub;
		}

		// Add the specified tags as aliases if not
		// already a substitute
		$names = trim($tag_string);
		$names = preg_split("/(,|;)/", $names);

		$tags = array();
		foreach ($names as $name)
		{
			$nrm = $this->normalize($name);

			$tags[] = $nrm;

			if (isset($subs[$nrm]))
			{
				continue; // Substitution already exists
			}

			$sub = new Alias;
			$sub->name   = trim($name);
			$sub->slug   = trim($nrm);
			$sub->tag_id = $this->id;
			if (!$sub->save())
			{
				$this->addError($sub->getError());
			}
		}

		// Run through the old list of substitutions, finding any
		// not in the new list and delete them
		foreach ($subs as $key => $sub)
		{
			if (!in_array($key, $tags))
			{
				if (!$sub->delete())
				{
					$this->addError($sub->getError());
					return false;
				}
			}
		}

		// Get all possibly existing tags that are now aliases
		$ids = self::query()
			->whereIn('slug', $tags)
			->get();

		// Move associations on tag and delete tag
		foreach ($ids as $tag)
		{
			if ($tag->id != $this->id)
			{
				// Get all the associations to this tag
				// Loop through the associations and link them to a different tag
				Tagged::moveTo($tag->id, $this->id);

				// Get all the substitutions to this tag
				// Loop through the records and link them to a different tag
				Alias::moveTo($tag->id, $this->id);

				// Delete the tag
				$tag->delete();
			}
		}

		$this->tagged_count = $this->tagged()->count();
		$this->alias_count = $this->aliases()->count();

		return $this->save();
	}
}
