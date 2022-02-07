<?php

namespace App\Modules\News\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Users\Models\User;
use App\Modules\News\Events\AssociationCreated;
use App\Modules\News\Events\AssociationDeleted;
use App\Modules\Tags\Traits\Taggable;

/**
 * News model mapping to associations
 */
class Association extends Model
{
	use SoftDeletes, Taggable;

	/**
	 * The table to which the class pertains
	 * 
	 * @var  string
	 **/
	protected $table = 'newsassociations';

	/**
	 * The name of the "created at" column.
	 *
	 * @var  string
	 */
	const CREATED_AT = 'datetimecreated';

	/**
	 * The name of the "updated at" column.
	 *
	 * @var  string
	 */
	const UPDATED_AT = null;

	/**
	 * The name of the "deleted at" column.
	 *
	 * @var  string
	 */
	const DELETED_AT = 'datetimeremoved';

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
	 * The attributes that are mass assignable.
	 *
	 * @var  array
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * The event map for the model.
	 *
	 * @var  array
	 */
	protected $dispatchesEvents = [
		'created'  => AssociationCreated::class,
		'deleted'  => AssociationDeleted::class,
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'newsid'  => 'required|integer',
		'associd' => 'required|integer',
		'assoctype' => 'required|string|max:255',
	);

	/**
	 * Runs extra setup code when creating/updating a new model
	 *
	 * @return  void
	 */
	protected static function boot()
	{
		parent::boot();

		// Parse out hashtags and tag the record
		static::created(function ($model)
		{
			$keywords = $model->extractKeywords($model->comment, 0);
			$keywords = array_keys($keywords);

			if (!empty($keywords))
			{
				$tags = array();

				foreach ($keywords as $match)
				{
					if ($model->isTag($match))
					{
						$tags[] = $match;
					}
				}

				$model->setTags($tags);
			}
		});

		static::updated(function ($model)
		{
			$keywords = $model->extractKeywords($model->comment, 0);
			$keywords = array_keys($keywords);

			if (!empty($keywords))
			{
				$tags = array();

				foreach ($keywords as $match)
				{
					if ($model->isTag($match))
					{
						$tags[] = $match;
					}
				}

				$model->setTags($tags);
			}
		});
	}

	/**
	 * Defines a relationship to news article
	 *
	 * @return  object
	 */
	public function article()
	{
		return $this->belongsTo(Article::class, 'newsid');
	}

	/**
	 * Get the associated object
	 *
	 * @return  object
	 */
	public function getAssociatedAttribute()
	{
		$item = null;
		if ($this->assoctype == 'user')
		{
			$item = User::find($this->associd);
		}
		return $item;
	}

	/**
	 * Extract keywords from text
	 *
	 * @param  string  $string
	 * @param  integer $limit
	 * @return array
	 */
	public function extractKeywords($string, $limit = 10)
	{
		$stopWords = array(
			'1', '2', '3', '4', '5', '6', '7', '8', '9', '0',
			'a', 'about', 'above', 'across', 'after', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also', 'although', 'always', 'among', 'an', 'and', 'another', 'any', 'anybody', 'anyone', 'anything', 'anywhere', 'are', 'area', 'areas', 'around', 'as', 'ask', 'asked', 'asking', 'asks', 'at', 'away',
			'b', 'back', 'backed', 'backing', 'backs', 'be', 'became', 'because', 'become', 'becomes', 'been', 'before', 'began', 'behind', 'being', 'beings', 'best', 'better', 'between', 'big', 'both', 'but', 'by',
			'c', 'came', 'can', 'cannot', 'case', 'cases', 'certain', 'certainly', 'clear', 'clearly', 'come', 'could',
			'd', 'did', 'differ', 'different', 'differently', 'do', 'does', 'done', 'down', 'downed', 'downing', 'downs', 'during',
			'e', 'each', 'early', 'either', 'en', 'end', 'ended', 'ending', 'ends', 'enough', 'even', 'evenly', 'ever', 'every', 'everybody', 'everyone', 'everything', 'everywhere',
			'f', 'face', 'faces', 'far', 'felt', 'few', 'find', 'finds', 'first', 'for', 'four', 'from', 'full', 'fully', 'further', 'furthered', 'furthering', 'furthers',
			'g', 'gave', 'general', 'generally', 'get', 'gets', 'give', 'given', 'gives', 'go', 'going', 'good', 'goods', 'got', 'great', 'greater', 'greatest', 'group', 'grouped', 'grouping', 'groups',
			'h', 'had', 'has', 'have', 'having', 'he', 'her', 'here', 'herself', 'high', 'higher', 'highest', 'him', 'himself', 'his', 'how', 'however',
			'i', 'if', 'important', 'in', 'interest', 'interested', 'interesting', 'interests', 'into', 'is', 'it', 'its', 'itself',
			'j', 'just',
			'k', 'keep', 'keeps', 'kind', 'knew', 'know', 'known', 'knows',
			'l', 'la', 'large', 'largely', 'last', 'later', 'latest', 'least', 'less', 'let', 'lets', 'like', 'likely', 'long', 'longer', 'longest',
			'm', 'made', 'make', 'making', 'man', 'many', 'may', 'me', 'member', 'members', 'men', 'might', 'more', 'most', 'mostly', 'mr', 'mrs', 'much', 'must', 'my', 'myself',
			'n', 'necessary', 'need', 'needed', 'needing', 'needs', 'never', 'new', 'newer', 'newest', 'next', 'no', 'nobody', 'non', 'noone', 'not', 'nothing', 'now', 'nowhere', 'number', 'numbers',
			'o', 'of', 'off', 'often', 'old', 'older', 'oldest', 'on', 'once', 'one', 'only', 'open', 'opened', 'opening', 'opens', 'or', 'order', 'ordered', 'ordering', 'orders', 'other', 'others', 'our', 'out', 'over',
			'p', 'part', 'parted', 'parting', 'parts', 'per', 'perhaps', 'place', 'places', 'point', 'pointed', 'pointing', 'possible', 'present', 'presented', 'presenting', 'presents', 'problem', 'problems', 'put', 'puts',
			'q', 'quite',
			/*'r',*/'rather', 'really', 'right', 'right', 'room', 'rooms',
			's', 'said', 'same', 'saw', 'say', 'says', 'second', 'seconds', 'see', 'seem', 'seemed', 'seeming', 'seems', 'sees', 'several', 'shall', 'she', 'should', 'show', 'showed', 'showing', 'shows', 'side', 'sides', 'since', 'small', 'smaller', 'smallest', 'so', 'some', 'somebody', 'someone', 'something', 'somewhere', 'states', 'still', 'such', 'sure',
			't', 'take', 'taken', 'than', 'that', 'the', 'their', 'them', 'then', 'there', 'therefore', 'these', 'they', 'thing', 'things', 'think', 'thinks', 'this', 'those', 'though', 'thought', 'thoughts', 'three', 'through', 'thus', 'to', 'today', 'together', 'too', 'took', 'toward', 'turn', 'turned', 'turning', 'turns', 'two',
			'u', 'under', 'unless', 'until', 'up', 'upon', 'us', 'use', 'used', 'uses',
			'v', 'very',
			'w', 'want', 'wanted', 'wanting', 'wants', 'was', 'way', 'ways', 'we', 'well', 'wells', 'went', 'were', 'what', 'when', 'where', 'whether', 'which', 'while', 'who', 'whole', 'whose', 'why', 'will', 'with', 'within', 'without', 'work', 'worked', 'working', 'works', 'would',
			'x',
			'y', 'year', 'years', 'yet', 'you', 'young', 'younger', 'youngest', 'your', 'yours',
			'z'
		);
		//$stopWords = array('i','a','about','an','and','are','as','at','be','by','com','de','en','for','from','how','in','is','it','la','of','on','or','that','the','this','to','was','what','when','where','who','will','with','und','the','www');

		$string = preg_replace('/\s\s+/i', '', $string); // replace whitespace
		$string = trim($string); // trim the string
		$string = preg_replace('/[^a-zA-Z0-9 -]/', '', $string); // only take alphanumerical characters, but keep the spaces and dashes too…
		$string = strtolower($string); // make it lowercase
	
		preg_match_all('/\b.*?\b/i', $string, $matchWords);
		$matchWords = $matchWords[0];
		
		foreach ($matchWords as $key => $item)
		{
			$item = trim($item);
			if ($item == '' || in_array(strtolower($item), $stopWords))// || strlen($item) <= 3)
			{
				unset($matchWords[$key]);
			}
		}

		$wordCountArr = array();
		if (is_array($matchWords))
		{
			foreach ($matchWords as $key => $val)
			{
				$val = strtolower($val);
				if (isset($wordCountArr[$val]))
				{
					$wordCountArr[$val]++;
				}
				else
				{
					$wordCountArr[$val] = 1;
				}
			}
		}
		arsort($wordCountArr);
		if ($limit)
		{
			$wordCountArr = array_slice($wordCountArr, 0, $limit);
		}

		return $wordCountArr;
	}
}
