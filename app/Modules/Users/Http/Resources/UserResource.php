<?php

namespace App\Modules\Users\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Users\Events\UserBeforeDisplay;

/**
 * @mixin \App\Modules\Users\Models\User
 */
class UserResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array<string,mixed>
	 */
	public function toArray($request)
	{
		event($event = new UserBeforeDisplay($this->resource));

		$this->resource = $event->getUser();

		$data = parent::toArray($request);

		unset($data['api_token']);

		$data['api'] = route('api.users.read', ['id' => $this->id]);
		//$data['uri'] = route('site.users.account', ['id' => $this->id]);

		if ($request->has('search'))
		{
			$data['search'] = $request->input('search');
		}
		$data['datecreated'] = $this->datecreated;
		$data['datelastseen'] = $this->getUserUsername()->hasVisited() ? $this->datelastseen : null;
		$data['dateremoved'] = $this->getUserUsername()->trashed() ? $this->dateremoved : null;
		$data['unixid'] = $this->unixid;
		$data['username'] = $this->username;
		$data['email'] = $this->email;

		$data['notes'] = $this->notes;
		$data['roles'] = $this->roles;
		$data['facets'] = $this->facets;

		// Permissions check
		$data['can']['edit']   = false;
		$data['can']['delete'] = false;
		if (!empty($this->module_permissions))
		{
			$data['module_permissions'] = $this->module_permissions;
		}
		$data['access'] = $this->getAuthorisedViewLevels();

		$user = auth()->user();
		if (!$user)
		{
			if (auth()->guard('api')->check())
			{
				$user = auth()->guard('api')->user();
			}
		}

		if ($user)
		{
			$data['can']['edit']   = ($user->can('edit users') || ($user->can('edit.own users') && $this->id == $user->id));
			$data['can']['delete'] = $user->can('delete users');
		}

		return $data;
	}
}
