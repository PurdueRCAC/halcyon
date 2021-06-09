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

		$data['api'] = route('api.storage.directories.read', ['id' => $this->id]);
		$data['group'] = $this->group;
		$data['unixgroup'] = $this->unixgroup->toArray();
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

		$data['user'] = array(
			'id' => 0,
			'name' => 'root'
		);

		if ($this->owner)
		{
			$data['user'] = array(
				'id' => $this->owner->id,
				'name' => $this->owner->username
			);
		}

		// [!] Legacy compatibility
		if (request()->segment(1) == 'ws')
		{
			$data['path'] = $this->fullPath;
			$data['id'] = '/ws/storagedir/' . $this->id;

			$data['group']['id'] = '/ws/group/' . $this->id;

			$data['resource'] = array(
				'id' => '/ws/resource/' . $this->resourceid,
				'name' => $this->resource->name
			);

			$data['user']['uid'] = $data['user']['id'];
			unset($data['user']['id']);

			if (!empty($data['unixgroup']))
			{
				$data['unixgroup']['id'] = '/ws/unixgroup/' . $data['unixgroup']['id'];
				$data['unixgroup']['gid'] = $data['unixgroup']['groupid'];
				unset($data['unixgroup']['groupid']);
			}

			$data['childdirs'] = array();
			foreach ($data['children'] as $child)
			{
				$data['childdirs'][] = '/ws/storagedir/' . $child->id;
			}
			unset($data['children']);
		}

		return $data;
	}
}