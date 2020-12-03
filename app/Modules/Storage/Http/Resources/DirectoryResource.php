<?php

namespace App\Modules\Storage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DirectoryResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$data = parent::toArray($request);

		$data['group'] = $this->group;
		$data['unixgroup'] = $this->unixgroup;
		$data['autounixgroup'] = $this->autounixgroup;

		// Set directory type
		if (!$this->bytes)
		{
			$data['type'] = 'directory';
		}
		else
		{
			$data['type'] = 'fileset';
		}

		$data['permissions'] = $this->unixPermissions;
		$data['mode'] = $this->mode;
		$data['acl'] = $this->acl;
		$data['children'] = $this->children;
		$data['latestusage'] = $this->usage()->orderBy('id', 'desc')->first();

		return $data;
	}
}