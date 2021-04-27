<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use App\Modules\History\Traits\Historable;

/**
 * Finder Node
 */
class Node extends Model
{
	use Historable;

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
	protected $primaryKey = 'nid';

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'node';

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
		'type' => 'required|string|max:32',
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
	 * Field Service Paragraphs
	 *
	 * @return  object
	 */
	public function fieldServiceParagraphs()
	{
		return $this->hasMany(NodeFieldServiceParagraph::class, 'entity_id')->where('deleted', '=', 0);
	}

	/**
	 * Field Facet Matches
	 *
	 * @return  object
	 */
	public function fieldFacetMatches()
	{
		return $this->hasMany(NodeFieldFacetMatches::class, 'entity_id')->where('deleted', '=', 0);
	}

	/**
	 * Field Data
	 *
	 * @return  object
	 */
	public function fieldData()
	{
		return $this->hasMany(NodeFieldData::class, 'nid');
	}

	/**
	 * Field Facet Matches
	 *
	 * @return  object
	 */
	public function fieldSummary()
	{
		return $this->hasOne(NodeFieldSummary::class, 'entity_id')->where('deleted', '=', 0);
	}

	/**
	 * Field Facet Matches
	 *
	 * @return  array
	 */
	public static function services()
	{
		/*$values = [
			'type' => 'service'
		];

		$nodes = Drupal::entityTypeManager()
			->getListBuilder('node')
			->getStorage()
			->loadByProperties($values);*/
		$nodes = self::query()
			->where('type', '=', 'services')
			->get();

		$services = []; // where we will build the service data

		// this is how to get the node info
		$display = \Drupal::entityTypeManager()
			->getStorage('entity_view_display')
			->load('node.service.default');

		$paragraph_display = \Drupal::entityTypeManager()
			->getStorage('entity_view_display')
			->load("paragraph.service_paragraphs.default");

		foreach ($nodes as $node)
		{
			$s = [];
			$s["id"] = $node->nid->value;
			$s["title"] = $node->title->value;

			// get the facet matches
			$s["facet_matches"] = [];
			foreach ($node->fieldFacetMatches as $match)
			{
				$s["facet_matches"][] = $match->target_id;
			}
			$s["summary"] = $node->fieldSummary->field_summary_value;

			// get the service_paragraphs
			$paragraph = $node->fieldServiceParagraphs->first();

			if ($paragraph)
			{
				$pdoutput = [];
				$paragraph = $paragraph->get('entity')->getTarget();

				// the order of the paragraphs is in $paragraph_display[
				// the fields are array_keys($paragraph_display["content"])
				// the weights are $paragraph_display["content"][$field]["weight"]
				$pdcontent = $paragraph_display->toArray()["content"];

				foreach ($pdcontent as $machine_name => $field_data)
				{
					$field_data = [];
					if (sizeof($paragraph->get($machine_name)->getValue()) > 0)
					{
						$field_data["value"] = $paragraph->get($machine_name)->getValue()[0]["value"];
					}

					$field_config = \Drupal::entityManager()
						->getStorage('field_config')
						->load('paragraph.service_paragraphs.' . $machine_name)
						->toArray();

					$field_data["label"] = $field_config["label"];
					$field_data["weight"] = $pdcontent[$machine_name]["weight"];

					$pdoutput[$machine_name] = $field_data;
				}

				$s['field_data'] = $pdoutput;
			}

			array_push($services, $s);
		}

		$title = [];
		foreach ($services as $key => $row)
		{
			$title[$key] = $row['title'];
		}
		array_multisort($title, SORT_ASC, $services);

		return $services;
	}
}
