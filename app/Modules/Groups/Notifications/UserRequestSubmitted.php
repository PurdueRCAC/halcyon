<?php

namespace App\Modules\Groups\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Modules\Groups\Models\UserRequest;
use App\Modules\Groups\Models\Member;
use App\Modules\Groups\Mail\UserRequestSubmitted as UserRequestSubmittedMail;

class UserRequestSubmitted extends Notification
{
	use Queueable;

	/**
	 * The user request
	 * 
	 * @var UserRequest $userrequest
	 */
	private $userrequest;

	/**
	 * Constructor
	 * 
	 * @param   UserRequest $userrequest
	 * @return  void
	 */
	public function __construct(UserRequest $userrequest)
	{
		$this->userrequest = $userrequest;
	}

	/**
	 * What methods can this notificaiton be sent
	 * 
	 * @param   object  $notifiable
	 * @return  array<int,string>
	 */
	public function via($notifiable)
	{
		return ['database'];
	}

	/**
	 * Generate a message formatted for database
	 * 
	 * @param   object  $notifiable
	 * @return  array<string,string>
	 */
	public function toArray($notifiable)
	{
		$member = Member::query()
			->where('userrequestid', '=', $this->userrequest->id)
			->where('userid', '=', $this->userrequest->userid)
			->first();

		$group = $member->group;

		$title = trans('groups::groups.groups');
		$content = '<a href="' . route('site.users.account.section.show.subsection', ['section' => 'groups', 'id' => $group->id, 'subsection' => 'members']) . '">' . trans('groups::groups.group has pending requests', ['group' => $group->name]) . '</a>';

		return [
			'title' => $title,
			'data' => $content
		];
	}
}
