<?php

namespace App\Modules\Storage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * @mixin \App\Modules\Storage\Models\Purchase
 */
class PurchaseResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array<string,mixed>
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['api'] = route('api.storage.purchases.read', ['id' => $this->id]);
		$data['seller'] = $this->seller;
		$data['counter'] = $this->counter;

		return $data;
	}
}