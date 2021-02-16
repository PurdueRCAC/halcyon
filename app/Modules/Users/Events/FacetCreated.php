<?php
namespace App\Modules\Users\Events;

use App\Modules\Users\Models\Facet;

class FacetCreated
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
