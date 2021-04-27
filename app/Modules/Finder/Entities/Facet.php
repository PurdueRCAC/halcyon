<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Facet model
 */
class Facet extends Model
{
	use Historable;

	/**
	 * Timestamps
	 *
	 * @var  bool
	 **/
	public $timestamps = false;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'finder_facets';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'name';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
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
	 * @var  array
	 */
	protected $rules = array(
		'name' => 'required'
	);

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
			if (sizeof($term->get('field_control_type')->getValue()) > 0)
			{
				$tid = $term->get('field_control_type')->getValue()[0]["target_id"];
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
