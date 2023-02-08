<?php

namespace App\Modules\Queues\Events;

use Illuminate\Http\Response;

class QosList
{
	/**
	 * @var array
	 */
	public $rows;

	/**
	 * @var string
	 */
	public $format = '';

	/**
	 * @var Response
	 */
	public $response;

	/**
	 * Constructor
	 *
	 * @param  array  $rows
	 * @param  string $format
	 * @return void
	 */
	public function __construct($rows, $format = '')
	{
		$this->rows = $rows;
		$this->format = $format;
	}
}
