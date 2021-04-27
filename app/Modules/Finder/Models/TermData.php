<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Finder Term
 */
class Term extends Model
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
	protected $table = 'taxonomy_term_data';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $guarded = [
		'tid'
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var  array
	 */
	protected $rules = array(
		'revision_id' => 'required',
		'vid' => 'required'
	);

	/**
	 * Field of science
	 *
	 * @return  object
	 */
	public function children()
	{
		return $this->belongsTo(Field::class, 'fieldofscienceid');
	}

	/**
	 * Group
	 *
	 * @return  object
	 */
	public function parent()
	{
		return $this->belongsTo(Group::class, 'groupid');
	}

	/**
	 * Group
	 *
	 * @return  object
	 */
	public function fieldControlType()
	{
		return $this->hasOne(TermFieldControlType::class, 'entity_id', 'id')->where('revision_id', '=', );
	}

	/** 
	 * Return an array of the facets tree
	 * 
	 * @return  array  the facet tree
	 */
	public static function tree()
	{
		/*$terms = Drupal::entityTypeManager()
			->getStorage('taxonomy_term')
			->loadTree("facets", 0, null, true);*/
			//  $vid, $parent, $max_depth, $load_entities);

		$terms = self::all();

		// extract data for all of the terms
		foreach ($terms as $term)
		{
			if (sizeof($term->fieldControlType->getValue()) > 0)
			{
				$tid = $term->fieldControlType->getValue()[0]["target_id"];
				$control_type = Term::load($tid)->getName();
			}
			else
			{
				$control_type = null;
			}

			$term_data[] = [
				'id' => $term->tid->value,
				'name' => $term->name->value,
				"control_type" => $control_type,
				'parent' => $term->parents[0], // there will only be one
				'weight' => $term->weight->value,
				'selected' => false,
				'description' => $term->getDescription()
			];
		}

		// find the questions and add choices array
		$questions = [];

		foreach ($term_data as $td)
		{
			if ($td['parent'] == '0')
			{
				$td['choices'] = [];
				array_push($questions, $td);
			}
		}

		$temp_questions = [];

		// get the facets for each of the questions
		foreach ($questions as $q)
		{
			foreach ($term_data as $td)
			{
				if ($td['parent'] == $q['id'])
				{
					array_push($q['choices'], $td);
				}
			}

			// sort the choices by weight ascending
			$weight = [];
			foreach ($q['choices'] as $key => $row)
			{
				$weight[$key] = $row['weight'];
			}

			array_multisort($weight, SORT_ASC, $q['choices']);
			array_push($temp_questions, $q);
		}

		$questions = $temp_questions;

		// sort the questions by weight
		$weight = [];
		foreach ($questions as $key => $row)
		{
			$weight[$key] = $row['weight']; // convert to number
		}
		array_multisort($weight, SORT_ASC, $questions);

		return $questions;
	}
}
