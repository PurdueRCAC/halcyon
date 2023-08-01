<?php

namespace App\Modules\Search\Models;

use Carbon\Carbon;

/**
 * Model class for a search result
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
	 * @var  Carbon|null
	 */
	public $created_at;

	/**
	 * Updated
	 *
	 * @var  Carbon|null
	 */
	public $updated_at;

	/**
	 * Constructor
	 *
	 * @param string $category
	 * @param int $weight
	 * @param string $title
	 * @param string $text
	 * @param string $route
	 * @param Carbon|null $created_at
	 * @param Carbon|null $updated_at
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
