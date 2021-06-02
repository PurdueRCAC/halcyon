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
			$data['id'] = '/ws/messagequeue/' . $data['id'];
			$data['messagequeuetype'] = '/ws/messagequeuetype/' . $data['messagequeuetypeid'];
			$data['datetimesubmitted'] = $this->datetimesubmitted->toDateTimeString();
			$data['datetimestarted'] = $this->datetimestarted->toDateTimeString();
			$data['datetimecompleted'] = $this->datetimecompleted->toDateTimeString();
			$data['submitted'] = $data['datetimesubmitted'];
			$data['started'] = $this->started() ? $data['datetimestarted'] : '0000-00-00 00:00:00';
			$data['completed'] = $this->completed() ? $data['datetimecompleted'] : '0000-00-00 00:00:00';
			$data['user'] = '/ws/user/' . $data['userid'];
			$data['targetobject'] = '/ws/' . $this->type->classname . '/' . $data['targetobjectid'];
		}

		return $data;
	}
}