<?php
namespace App\Modules\Finder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\History\Traits\Historable;

/**
 * Finder Node
 *
 * @property int    $id
 * @property string $name
 * @property string $control_type
 * @property int    $parent
 * @property int    $weight
 * @property int    $status
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Facet extends Model
{
	use Historable;

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
	public static $orderBy = 'weight';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public static $orderDir = 'asc';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int,string>
	 */
	protected $guarded = [
		'id'
	];

	/**
	 * Choices
	 *
	 * @return  HasMany
	 */
	public function choices(): HasMany
	{
		return $this->hasMany(self::class, 'parent');
	}

	/**
	 * Service Facet matches
	 *
	 * @return  HasMany
	 */
	public function services(): HasMany
	{
		return $this->hasMany(ServiceFacet::class, 'facet_id');
	}

	/** 
	 * Return an array of the facets tree
	 * 
	 * @return  array  the facet tree
	 */
	public static function tree(): array
	{
		/*if (is_file(public_path('files/facettree.json')))
		{
			$data = json_decode(file_get_contents(public_path('files/facettree.json')));

			foreach ($data as $facet)
			{
				$datas[$facet->id] = $facet;
			}

			ksort($datas);
			$data = $datas;

			foreach ($data as $facet)
			{
				$f = Facet::find($facet->id);

				if (!$f || $f->id)
				{
					$f = new Facet;
					$f->create([
						'id' => $facet->id,
						'name' => $facet->name,
						'control_type' => $facet->control_type,
						'parent' => $facet->parent,
						'weight' => $facet->weight,
						'description' => $facet->description
					]);
				}

				foreach ($facet->choices as $choice)
				{
					$c = Facet::find($choice->id);

					if (!$c || !$c->id)
					{
						$c = new Facet;
						$c->create([
							'id' => $choice->id,
							'name' => $choice->name,
							'parent' => $choice->parent,
							'weight' => $choice->weight
						]);
					}
				}
			}

			return $data;
		}*/

		$terms = self::query()
			->where('parent', '=', 0)
			->where('status', '=', 1)
			->orderBy('weight', 'asc')
			->get();

		$questions = [];

		// find the questions and add choices array
		foreach ($terms as $term)
		{
			$choices = $term->choices()
				->where('status', '=', 1)
				->orderBy('weight', 'asc')
				->get();
			
			$c = array();
			foreach ($choices as $choice)
			{
				$c[] = [
					'id' => $choice->id,
					'name' => $choice->name,
					"control_type" => $choice->control_type,
					'parent' => $choice->parent,
					'weight' => $choice->weight,
					'description' => $choice->description
				];
			}

			$questions[] = [
				'id' => $term->id,
				'name' => $term->name,
				"control_type" => $term->control_type,
				'parent' => $term->parent,
				'weight' => $term->weight,
				'selected' => false,
				'description' => $term->description,
				'choices' => $c
			];
		}

		return $questions;
	}

	/**
	 * The "booted" method of the model.
	 *
	 * @return void
	 */
	protected static function booted(): void
	{
		static::creating(function ($model)
		{
			$result = self::query()
				->select(DB::raw('MAX(weight) + 1 AS seq'))
				->where('parent', '=', $model->parent)
				->first()
				->seq;

			$model->setAttribute('weight', (int)$result);
		});

		static::deleted(function ($model)
		{
			foreach ($model->choices as $choice)
			{
				$choice->delete();
			}

			foreach ($model->services as $service)
			{
				$service->delete();
			}
		});
	}
}
