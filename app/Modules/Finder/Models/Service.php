<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;

/**
 * Finder Node
 */
class Service extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'finder_services';

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public static $orderBy = 'title';

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
		'type' => 'required|string|max:255',
		'summary' => 'nullable|string|max:1200',
		'status' => 'nullable|integer',
	);

	/**
	 * Field Service Paragraphs
	 *
	 * @return  object
	 */
	public function fields()
	{
		return $this->hasMany(ServiceField::class, 'service_id');
	}

	/**
	 * Field Service Paragraphs
	 *
	 * @return  object
	 */
	public function facets()
	{
		return $this->hasMany(ServiceFacet::class, 'service_id');
	}

	/**
	 * Field Facet Matches
	 *
	 * @return  array
	 */
	public static function servicelist()
	{
		/*if (is_file(public_path('files/servicelist.json')))
		{
			$facet_map = [
				4 => 3,
				5 => 4,
				//13 => 5,
				14 => 6,
				15 => 7,
				//20 => 8,
				21 => 9,
				22 => 10,
				//23 => 11,
				24 => 12,
				25 => 13,
				//26 => 14,
				27 => 15,
				28 => 16,
				29 => 17,
				30 => 18,
				//33 => 19,
				34 => 20,
				35 => 21,
				//36 => 22,
				37 => 23,
				38 => 24,
				//39 => 25,
				40 => 26,
				41 => 27,
				//42 => 28,
				43 => 29,
				44 => 30
			];

			$data = json_decode(file_get_contents(public_path('files/servicelist.json')));

			foreach ($data as $service)
			{
				foreach ($service->field_data as $name => $field)
				{
					$field = Field::findByName($name);

					if (!$field)
					{
						continue;
					}

					$fs = ServiceField::findByServiceAndField($service->id, $field->id);

					if (!$fs || ! $fs->id)
					{
						$fs = new ServiceField;
						$fs->service_id = $service->id;
						$fs->field_id = $field->id;
						$fs->value = $field->value;
						$fs->save();
					}
				}

				foreach ($service->facet_matches as $facet_id)
				{
					if (!isset($facet_map[$facet_id]))
					{
						continue;
					}

					$fc = ServiceFacet::findByServiceAndFacet($service->id, $facet_map[$facet_id]);

					if (!$fc || !$fc->id)
					{
						$fc = new ServiceFacet;
						$fc->service_id = $service->id;
						$fc->facet_id = $facet_map[$facet_id];
						$fc->save();
					}
				}
			}

			return $data;
		}*/

		$nodes = self::query()
			->orderBy('title', 'asc')
			->get();

		$services = []; // where we will build the service data

		foreach ($nodes as $node)
		{
			$s = [];
			$s['id'] = $node->id;
			$s['title'] = $node->title;
			$s['summary'] = $node->summary;

			// get the facet matches
			$s['facet_matches'] = [];
			foreach ($node->facets as $match)
			{
				$s['facet_matches'][] = $match->facet_id;
			}

			$fields = $node->fields()
				->get();

			if (count($fields))
			{
				$pdoutput = [];

				foreach ($fields as $field)
				{
					if (!$field->field || !$field->field->status)
					{
						continue;
					}

					$field_data = [];
					$field_data['value'] = $field->value;
					$field_data['label'] = $field->field->label;
					$field_data['weight'] = $field->field->weight;

					$pdoutput['field_' . $field->field->name] = $field_data;
				}

				$s['field_data'] = $pdoutput;
			}

			array_push($services, $s);
		}

		return $services;
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted()
	{
		static::deleted(function ($model)
		{
			foreach ($model->fields as $field)
			{
				$field->delete();
			}

			foreach ($model->facets as $facet)
			{
				$facet->delete();
			}
		});
	}
}
