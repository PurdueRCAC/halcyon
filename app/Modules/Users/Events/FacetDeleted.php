<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\Facet;

class FacetDeleted
{
	/**
	 * @var Facet
	 */
	public $facet;

	/**
	 * Constructor
	 *
	 * @param  Facet $facet
	 * @return void
	 */
	public function __construct(Facet $facet)
	{
		$this->facet = $facet;
	}
}
