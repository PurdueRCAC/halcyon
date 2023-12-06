<?php

namespace App\Modules\Users\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Users\Events\UserBeforeDisplay;

/**
 * @mixin \Illuminate\Notifications\DatabaseNotification
 */
class NotificationResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array<string,mixed>
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);
		$data['api'] = route('api.users.notifications.read', ['id' => $this->id]);

		return $data;
	}
}
