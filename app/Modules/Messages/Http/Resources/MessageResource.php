<?php

namespace App\Modules\Messages\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
	/**
	 * Transform the resource collection into an array.
	 *
	 * @param   \Illuminate\Http\Request  $request
	 * @return  array
	 */
	public function toArray($request)
	{
		$this->type;

		$data = parent::toArray($request);

		$data['api'] = route('api.messages.read', ['id' => $this->id]);
		$data['target'] = $this->target;

		$data['can']['edit']   = false;
		$data['can']['delete'] = false;

		if (auth()->user())
		{
			$data['can']['edit']   = auth()->user()->can('edit messages');
			$data['can']['delete'] = auth()->user()->can('delete messages');
		}

		if (!$this->started())
		{
			$data['datetimestarted'] = null;
		}

		if (!$this->completed())
		{
			$data['datetimecompleted'] = null;
		}

		// [!] Legacy compatibility
		if ($request->segment(1) == 'ws')
		{
			/*$data['id'] = '/ws/messagequeue/' . $data['id'];
			$data['messagequeuetype'] = '/ws/messagequeuetype/' . $data['messagequeuetypeid'];
			$data['datetimesubmitted'] = $this->datetimesubmitted->toDateTimeString();
			$data['datetimestarted'] = $this->datetimestarted->toDateTimeString();
			$data['datetimecompleted'] = $this->datetimecompleted->toDateTimeString();
			$data['submitted'] = $data['datetimesubmitted'];
			$data['started'] = $this->started() ? $data['datetimestarted'] : '0000-00-00 00:00:00';
			$data['completed'] = $this->completed() ? $data['datetimecompleted'] : '0000-00-00 00:00:00';
			$data['user'] = '/ws/user/' . $data['userid'];
			$data['targetobject'] = '/ws/' . $this->type->classname . '/' . $data['targetobjectid'];*/

			$data = array();
			$data['id'] = '/ws/messagequeue/' . $this->id;
			$data['messagequeuetype'] = '/ws/messagequeuetype/' . $this->messagequeuetypeid;
			$data['type'] = '/ws/messagequeuetype/' . $this->messagequeuetypeid;
			$data['submitted'] = $this->datetimesubmitted->toDateTimeString();
			$data['started'] = $this->started() ? $this->datetimestarted->toDateTimeString() : '0000-00-00 00:00:00';
			$data['completed'] = $this->completed() ? $this->datetimecompleted->toDateTimeString() : '0000-00-00 00:00:00';
			$data['targetobject'] = '/ws/' . ($this->type ? $this->type->classname : 'unknown') . '/' . $this->targetobjectid;
			$data['userid'] = '/ws/user/' . $data['userid'];
			$data['pid'] = $this->pid;
		}

		return $data;
	}
}