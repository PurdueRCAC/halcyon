<?php

namespace App\Modules\Search\Models;

use Carbon\Carbon;

/**
 * Model class for a search result
 *
 * @property string $category
 * @property int    $weight
 * @property string $title
 * @property string $text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $route
 */
class Result
{
	/**
	 * Category
	 *
	 * @var  string
	 */
	public $category;

	/**
	 * Route
	 *
	 * @var  string
	 */
	public $route;

	/**
	 * Title
	 *
	 * @var  string
	 */
	public $title;

	/**
	 * Text
	 *
	 * @var  string
	 */
	public $text;

	/**
	 * Search weight
	 *
	 * @var  int
	 */
	public $weight;

	/**
	 * Created
	 *
	 * @var  string
	 */
	public $created_at;

	/**
	 * Updated
	 *
	 * @var  string
	 */
	public $updated_at;

	/**
	 * Constructor
	 */
	public function __construct(string $category, int $weight, string $title, string $text, string $route, $created_at = null, $updated_at = null)
	{
		$this->category = $category;
		$this->weight = $weight;
		$this->title = $title;
		$this->text = $text;
		$this->route = $route;
		$this->created_at = $created_at;
		$this->updated_at = $updated_at;
	}
}
