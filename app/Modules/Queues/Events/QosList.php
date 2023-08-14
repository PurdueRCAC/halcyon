<?php

namespace App\Modules\Queues\Events;

use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Queues\Models\Qos;

class QosList
{
	/**
	 * @var array<int,Qos>|Collection<int,Qos>|LengthAwarePaginator<Qos>
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
	 * @param  arrayarray<int,Qos>|Collection<int,Qos>|LengthAwarePaginator<Qos>  $rows
	 * @param  string $format
	 * @return void
	 */
	public function __construct($rows, $format = '')
	{
		$this->rows = $rows;
		$this->format = $format;
	}
}
