<?php

namespace App\Modules\Resources\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Queues\Models\Scheduler;

class AssetResource extends JsonResource
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

		$data['children'] = $this->descendents;
		$data['priorchildren'] = $this->descendents()->onlyTrashed()->get();
		$data['subresources'] = $this->subresources;
		$data['priorsubresources'] = $this->subresources()->onlyTrashed()->get();
		$data['api'] = route('api.resources.read', ['id' => $this->id]);

		$subs = $this->subresources->pluck('id')->toArray();

		$scheduler = Scheduler::query()
			->whereIn('queuesubresourceid', $subs)
			->limit(1)
			->get()
			->first();

		if ($scheduler)
		{
			$data['scheduler'] = $scheduler;
		}

		// If type == storage
		if ($this->resourcetype == 2)
		{
			$storagebuckets = array();

			/*
			$sql = "SELECT SUM(storagedirpurchases.bytes) AS soldbytes, groups.name, storagedirpurchases.groupid FROM storagedirpurchases LEFT OUTER JOIN `groups` ON storagedirpurchases.groupid = groups.id WHERE (storagedirpurchases.datetimestop = '0000-00-00 00:00:00' OR storagedirpurchases.datetimestop > NOW()) AND (storagedirpurchases.datetimestart = '0000-00-00 00:00:00' OR storagedirpurchases.datetimestart < NOW()) AND storagedirpurchases.resourceid = '" . $this->db->escape_string($id) . "' GROUP BY groups.id";
			$data = array();

			$rows = $this->db->query($sql, $data);

			foreach ($data as $row)
			{
				$sql = "SELECT name FROM storagedirs WHERE groupid = '" . $this->db->escape_string($row['groupid']) . "' AND bytes <> 0 AND datetimeremoved = '0000-00-00' AND resourceid = '" . $this->db->escape_string($id) . "'";
				$data2 = array();
				$rows = $this->db->query($sql, $data2);

				$path = "";
				if ($rows == 1)
				{
					$path = $data2[0]['name'];
				}

				array_push($this->storagebuckets, array(
					'group' => array(
						'id'          => ROOT_URI . 'group/' . $row['groupid'],
						'name'        => $row['name'],),
						'soldbytes'   => $row['soldbytes'],
						'loanedbytes' => 0,
						'totalbytes'  => $row['soldbytes'],
						'path'        => $path,
					)
				);
			}

			$data = array();

			$sql = "SELECT SUM(storagedirloans.bytes) AS loanedbytes, groups.name, storagedirloans.groupid FROM storagedirloans LEFT OUTER JOIN `groups` ON storagedirloans.groupid = groups.id WHERE (storagedirloans.datetimestop = '0000-00-00 00:00:00' OR storagedirloans.datetimestop > NOW()) AND (storagedirloans.datetimestart = '0000-00-00 00:00:00' OR storagedirloans.datetimestart < NOW()) AND storagedirloans.resourceid = '" . $this->db->escape_string($id) . "' GROUP BY groups.id";

			$rows = $this->db->query($sql, $data);

			foreach ($data as $row)
			{
				$found = false;

				foreach ($this->storagebuckets as &$bucket)
				{
					if ($bucket['group']['id'] == ROOT_URI . 'group/' . $row['groupid'])
					{
						$bucket['loanedbytes'] = $row['loanedbytes'];
						$bucket['totalbytes'] += $row['loanedbytes'];
						$found = true;
						break;
					}
				}

				if (!$found)
				{
					$sql = "SELECT name FROM storagedirs WHERE groupid = '" . $this->db->escape_string($row['groupid']) . "' AND bytes <> 0 AND datetimeremoved = '0000-00-00' AND resourceid = '" . $this->db->escape_string($id) . "'";
					$data2 = array();
					$rows = $this->db->query($sql, $data2);

					$path = '';
					if ($rows == 1)
					{
						$path = $data2[0]['name'];
					}

					array_push($this->storagebuckets, array(
						'group' => array(
							'id'          => ROOT_URI . 'group/' . $row['groupid'],
							'name'        => $row['name'],),
							'soldbytes'   => 0,
							'loanedbytes' => $row['loanedbytes'],
							'totalbytes'  => $row['loanedbytes'],
							'path'        => $path,
						)
					);
				}
			}*/
		}

		return $data;
	}
}