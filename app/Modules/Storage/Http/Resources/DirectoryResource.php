<?php

namespace App\Modules\Storage\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

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
		$data['unixgroup'] = $this->unixgroup ? $this->unixgroup->toArray() : array();
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

		if (!$this->isTrashed())
		{
			$data['datetimeremoved'] = null;
		}
		if (!$this->isConfigured())
		{
			$data['datetimeconfigured'] = null;
		}

		// [!] Legacy compatibility
		if (request()->segment(1) == 'ws')
		{
			$data['path'] = $this->fullPath;
			$data['id'] = '/ws/storagedir/' . $this->id;
			$data['quota'] = $this->bytes;
			$data['group']['id'] = '/ws/group/' . $this->id;
			$data['parent'] = '/ws/storagedir/' . $this->parentstoragedirid;
			$data['created'] = $this->datetimecreated->toDateTimeString();
			if ($this->isTrashed())
			{
				$data['removed'] = $this->datetimeremoved->toDateTimeString();
			}
			else
			{
				$data['removed'] = '0000-00-00 00:00:00';
			}

			$data['resource'] = array(
				'id' => '/ws/resource/' . $this->resourceid,
				'name' => ($this->storageResource && $this->storageResource->resource ? $this->storageResource->resource->name : '')
			);

			$data['user']['uid'] = 0;
			if ($this->owner)
			{
				$data['user']['uid'] = $this->owner->getUserUsername()->unixid;
			}
			unset($data['user']['id']);

			if (!empty($data['unixgroup']))
			{
				$data['unixgroup']['id'] = '/ws/unixgroup/' . $data['unixgroup']['id'];
				$data['unixgroup']['gid'] = $data['unixgroup']['groupid'];
				$data['unixgroup']['created'] = $this->unixgroup->datetimecreated->toDateTimeString();
				$data['unixgroup']['removed'] = $this->unixgroup->isTrashed() ? $this->unixgroup->datetimeremoved->toDateTimeString() : '0000-00-00 00:00:00';
				unset($data['unixgroup']['groupid']);
				unset($data['unixgroup']['datetimecreated']);
				unset($data['unixgroup']['datetimeremoved']);
			}
			else
			{
				$data['unixgroup'] = array('id' => '/ws/unixgroup/0', 'name' => '');
			}

			if (!empty($data['group']))
			{
				$data['group']['id'] = '/ws/group/' . $data['group']['id'];
			}

			$data['autouserunixgroup'] = $data['autounixgroup'] ? $data['autounixgroup']->toArray() : array('id' => '/ws/unixgroup/0', 'name' => '');
			if ($this->autounixgroup)
			{
				$data['autouserunixgroup']['id'] = '/ws/unixgroup/' . $data['autouserunixgroup']['id'];
				$data['autouserunixgroup']['gid'] = $data['autouserunixgroup']['groupid'];
				$data['autouserunixgroup']['created'] = $this->autounixgroup->datetimecreated->toDateTimeString();
				$data['autouserunixgroup']['removed'] = $this->autounixgroup->isTrashed() ? $this->autounixgroup->datetimeremoved->toDateTimeString() : '0000-00-00 00:00:00';
				unset($data['autouserunixgroup']['groupid']);
				unset($data['autouserunixgroup']['datetimecreated']);
				unset($data['autouserunixgroup']['datetimeremoved']);
			}
			unset($data['autounixgroup']);

			$data['childdirs'] = array();
			foreach ($data['children'] as $child)
			{
				$data['childdirs'][] = '/ws/storagedir/' . $child->id;
			}
			unset($data['children']);

			$keys = [
				'resourceid', 'groupid', 'datetimecreated', 'datetimeremoved', 'datetimeconfigured', 'ownerread', 'ownerwrite',
				'groupread', 'groupwrite', 'publicread', 'publicwrite', 'parentstoragedirid', 'storageresourceid', 'api'
			];
			foreach ($keys as $key)
			{
				unset($data[$key]);
			}
			$data['date'] = Carbon::now()->toDateTimeString();
		}

		return $data;
	}
}